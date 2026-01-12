<?php

namespace App\Command;

use App\Entity\File;
use App\Entity\LEFundingMethod;
use App\Entity\LEPeriod;
use App\Entity\LocalWorkgroup;
use App\Entity\Project;
use App\Entity\State;
use App\Entity\Topic;
use App\Service\ProjectService;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\DBAL\DriverManager;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(name: 'app:zrl:migrate')]
class ZrlMigrateCommand extends Command
{
    protected ManagerRegistry $doctrine;
    protected ProjectService $projectService;

    public function __construct(ManagerRegistry $doctrine, ProjectService $projectService)
    {
        parent::__construct();
        $this->doctrine = $doctrine;
        $this->projectService = $projectService;
    }

    protected function configure()
    {
        $this
            ->setDescription('Migrate zrl data')
            ->addOption('dbhost', null, InputOption::VALUE_REQUIRED, 'Database host.')
            ->addOption('dbname', null, InputOption::VALUE_REQUIRED, 'Database name.')
            ->addOption('dbuser', null, InputOption::VALUE_REQUIRED, 'Database user.')
            ->addOption('dbpassword', null, InputOption::VALUE_REQUIRED, 'Database password.')
            ->addOption('dir-media', null, InputOption::VALUE_REQUIRED, 'Media directory location.')
            ->addOption('dir-binary', null, InputOption::VALUE_REQUIRED, 'Binary directory location.')
            ->addOption('dry-run', null, InputOption::VALUE_NONE, 'Simulate procedure without actually writing anything.')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $dbhost = $input->getOption('dbhost');
        $dbname = $input->getOption('dbname');
        $dbuser = $input->getOption('dbuser');
        $dbpassword = $input->getOption('dbpassword');
        $dirMedia = $input->getOption('dir-media');
        $dirBinary = $input->getOption('dir-binary');
        $dryRun = $input->getOption('dry-run');

        $successCount = 0;
        $missingPlans = [];
        $missingTopics = [];

        $io->info(sprintf('Connecting to database %s@%s...', $dbhost, $dbname));

        $externalConnection = DriverManager::getConnection([
            'url' => sprintf('mysql://%s:%s@%s/%s', $dbuser, $dbpassword, $dbhost, $dbname),
            'charset' => 'utf8mb4',
        ]);

        $oldProjects = $externalConnection->fetchAllAssociative('SELECT project_id, identifier, period_id FROM zrl_project');

        foreach ($oldProjects as $oldProject) {

            $io->info(sprintf('Migrating project "%s" (ID: %s)...', $oldProject['identifier'], $oldProject['project_id']));

            $oldProject = $externalConnection->fetchAssociative('SELECT * FROM zrl_project WHERE project_id = :project_id', [
                'project_id' => $oldProject['project_id'],
            ]);

            $oldPeriod = $externalConnection->fetchAssociative('SELECT * FROM zrl_period WHERE period_id = :period_id', [
                'period_id' => $oldProject['period_id'],
            ]);

            $oldPlan = $externalConnection->fetchAssociative('SELECT * FROM zrl_plan WHERE plan_id = :plan_id', [
                'plan_id' => $oldProject['plan_id'],
            ]);

            $oldProject2Keywords = $externalConnection->fetchAllAssociative('SELECT * FROM zrl_project2keyword WHERE project_id = :project_id', [
                'project_id' => $oldProject['project_id'],
            ]);

            $oldProject2Links = $externalConnection->fetchAllAssociative('SELECT * FROM zrl_project2link WHERE project_id = :project_id ORDER BY ordinal_nr ASC', [
                'project_id' => $oldProject['project_id'],
            ]);

            $oldProject2Leaders = $externalConnection->fetchAllAssociative('SELECT * FROM zrl_project2leader WHERE project_id = :project_id', [
                'project_id' => $oldProject['project_id'],
            ]);

            $oldProject2Regions = $externalConnection->fetchAllAssociative('SELECT * FROM zrl_project2region WHERE project_id = :project_id', [
                'project_id' => $oldProject['project_id'],
            ]);

            $oldProjectTrans = $externalConnection->fetchAssociative('SELECT * FROM zrl_project_trans WHERE project_id = :project_id', [
                'project_id' => $oldProject['project_id'],
                'language_id' => 'de',
            ]);

            $oldProject2Medias = $externalConnection->fetchAllAssociative('SELECT * FROM zrl_project2media WHERE project_id = :project_id ORDER BY ordinal_nr ASC', [
                'project_id' => $oldProject['project_id'],
            ]);

            $oldProject2Binaries = $externalConnection->fetchAllAssociative('SELECT * FROM zrl_project2binary WHERE project_id = :project_id ORDER BY ordinal_nr ASC', [
                'project_id' => $oldProject['project_id'],
            ]);

            $project = new Project();
            $project
                ->setCreatedAt(new \DateTime($oldProject['publishing_date']))
                ->setSource('zrl')
                ->setForeignId($oldProject['project_id'])
            ;

            $project = $this->doctrine->getManager()->getRepository(Project::class)->findOneBy([
                'source' => 'zrl',
                'foreignId' => $oldProject['project_id'],
            ]) ?? $project;

            $project = $this->doctrine->getManager()->getRepository(Project::class)->findOneBy([
                'source' => 'legacy_import',
                'title' => $oldProjectTrans['title'],
            ]) ?? $project;

            if($project->getId()) {
                $io->info(sprintf('Found existing project "%s" (ID: %s)...', $project->getTitle(), $project->getId()));
            }

            $project
                ->setIsPublic($oldProject['show_web'] === '1')
                ->setProjectCode($oldProject['identifier'])
                ->setTitle($oldProjectTrans['title'])
                ->setKeywords('')
                ->setDescription($oldProjectTrans['textlong'])
                ->setLearningExperience($oldProjectTrans['experience'])
                ->setResultsQuality($oldProjectTrans['result'])
                ->setFundingMethod($oldProjectTrans['implementation'])
                ->setInitialContextGoals($oldProjectTrans['target'])
                ->setInitialContext($oldProjectTrans['initialposition'])
            ;

            if($oldProject['cost']) {

                $normalized = preg_replace('/[^\d,.-]/', '', $oldProject['cost']);
                $normalized = preg_replace('/,(–|-)+$/u', ',00', $normalized);
                $normalized = str_replace('.', '', $normalized);
                $normalized = str_replace(',', '.', $normalized);

                if(count(explode('.', (string)$normalized)) <= 2) {
                    $project->setProjectCosts((float)$normalized);
                } else {
                    $io->warning(sprintf('Cannot normalize project costs "%s" of project "%s" (ID: %s)', $oldProject['cost'], $oldProject['identifier'], $oldProject['project_id']));
                }

            }

            if($oldProject['funding'] && $project->getProjectCosts()) {

                $normalized = preg_replace('/[^\d,.-]/', '', $oldProject['funding']);
                $normalized = preg_replace('/,(–|-)+$/u', ',00', $normalized);
                $normalized = str_replace('.', '', $normalized);
                $normalized = str_replace(',', '.', $normalized);

                if(count(explode('.', (string)$normalized)) <= 2) {

                    $project->setFinancing([
                        [
                            'value' => min(round(100 / $project->getProjectCosts() * (float)$normalized, 2), 100),
                            'id' => 'costsGap'
                        ]
                    ]);

                } else {
                    $io->warning(sprintf('Cannot normalize financing "%s" of project "%s" (ID: %s)', $oldProject['funding'], $oldProject['identifier'], $oldProject['project_id']));
                }

            }

            $normalizeDate = function (string $input): ?string
            {
                $s = trim($input);

                $s = preg_replace('/\s+/u', ' ', $s);

                $s = preg_replace('/^[^\d]+(?=\d)/u', '', $s);

                $s = preg_replace('/\.\.+/', '.', $s);

                $s = preg_replace('/^(\d{1,2})\.(\d{2})(\d{4})$/', '$1.$2.$3', $s);

                $s = preg_replace('/^(\d{2})(\d{2})\.(\d{4})$/', '$1.$2.$3', $s);

                $s = preg_replace('/(\d{1,2})\.(\d{1,2})\-(\d{2,4})$/', '$1.$2.$3', $s);

                $s = preg_replace('/(\d{1,2})\.\s*(\d{1,2})\.\s*(\d{2,4})/', '$1.$2.$3', $s);

                $s = preg_replace('/^(\d{1,2})\s+(\d{1,2})\s+(\d{2,4})$/', '$1.$2.$3', $s);

                $lower = mb_strtolower($s, 'UTF-8');

                if (preg_match('/\b(frühling|fruehling|sommer|herbst|winter)\b.*?(\d{4})/iu', $lower, $m)) {
                    $season = $m[1];
                    $year   = (int)$m[2];

                    $seasonStart = [
                        'frühling'  => [3, 1],
                        'fruehling' => [3, 1],
                        'sommer'    => [6, 1],
                        'herbst'    => [9, 1],
                        'winter'    => [12, 1],
                    ];

                    [$month, $day] = $seasonStart[$season] ?? [1, 1];

                    $dt = \DateTime::createFromFormat('!Y-m-d', sprintf('%04d-%02d-%02d', $year, $month, $day));
                    return $dt ? $dt->format('Y-m-d') : null;
                }

                $monthMap = [
                    'jänner' => 1, 'jaenner' => 1, 'januar' => 1, 'jan' => 1,
                    'februar' => 2, 'feb' => 2,
                    'märz' => 3, 'maerz' => 3, 'mär' => 3, 'mrz' => 3,
                    'april' => 4, 'apr' => 4,
                    'mai' => 5,
                    'juni' => 6, 'jun' => 6,
                    'juli' => 7, 'jul' => 7,
                    'august' => 8, 'aug' => 8,
                    'september' => 9, 'sept' => 9, 'sep' => 9,
                    'oktober' => 10, 'okt' => 10,
                    'november' => 11, 'nov' => 11,
                    'dezember' => 12, 'dez' => 12,
                ];

                if (preg_match('/^(\d{1,2})\.\s*([^\d\s]+)\s+(\d{2,4})/u', $s, $m)) {
                    $day   = (int) $m[1];
                    $name  = rtrim(mb_strtolower($m[2], 'UTF-8'), '.');
                    $yearS = $m[3];

                    if (!isset($monthMap[$name])) {
                        return null;
                    }
                    $month = $monthMap[$name];

                    if (strlen($yearS) === 2) {
                        $yy   = (int) $yearS;
                        $year = $yy <= 30 ? 2000 + $yy : 1900 + $yy;
                    } else {
                        $year = (int) $yearS;
                    }

                    $dt = \DateTime::createFromFormat('!Y-m-d', sprintf('%04d-%02d-%02d', $year, $month, $day));
                    return $dt ? $dt->format('Y-m-d') : null;
                }

                if (preg_match('/^([[:alpha:]\.]+)\s+(\d{2,4})$/u', $s, $m)) {
                    $name  = rtrim(mb_strtolower($m[1], 'UTF-8'), '.');
                    $yearS = $m[2];

                    $month = $monthMap[$name] ?? null;
                    if ($month !== null) {
                        if (strlen($yearS) === 2) {
                            $yy   = (int)$yearS;
                            $year = $yy <= 30 ? 2000 + $yy : 1900 + $yy;
                        } else {
                            $year = (int)$yearS;
                        }

                        $dt = \DateTime::createFromFormat('!Y-m-d', sprintf('%04d-%02d-01', $year, $month));
                        return $dt ? $dt->format('Y-m-d') : null;
                    }
                }

                if (preg_match('/^(\d{1,2})\.(\d{1,2})\.(\d{2,4})$/', $s, $m)) {
                    $day  = (int)$m[1];
                    $mon  = (int)$m[2];
                    $yearS = $m[3];

                    // fix obviously swapped day/month like "11.20.21" -> 20.11.2021
                    if ($mon > 12 && $day <= 12) {
                        [$day, $mon] = [$mon, $day];
                    }

                    if (strlen($yearS) === 2) {
                        $yy   = (int)$yearS;
                        $year = $yy <= 30 ? 2000 + $yy : 1900 + $yy;
                    } else {
                        $year = (int)$yearS;
                    }

                    $dt = \DateTime::createFromFormat('!Y-m-d', sprintf('%04d-%02d-%02d', $year, $mon, $day));
                    return $dt ? $dt->format('Y-m-d') : null;
                }

                if (preg_match('/^(\d{1,2})[\.\/\-](\d{2,4})$/', $s, $m)) {
                    $mon   = (int)$m[1];
                    $yearS = $m[2];

                    if (strlen($yearS) === 2) {
                        $yy   = (int)$yearS;
                        $year = $yy <= 30 ? 2000 + $yy : 1900 + $yy;
                    } else {
                        $year = (int)$yearS;
                    }

                    $dt = \DateTime::createFromFormat('!Y-m-d', sprintf('%04d-%02d-01', $year, $mon));
                    return $dt ? $dt->format('Y-m-d') : null;
                }

                if (preg_match('/^(\d{1,2})\.(\d{1,2})\.(\d{2,4})\b/u', $s, $m)) {
                    $day   = (int)$m[1];
                    $mon   = (int)$m[2];
                    $yearS = $m[3];

                    if (strlen($yearS) === 2) {
                        $yy   = (int)$yearS;
                        $year = $yy <= 30 ? 2000 + $yy : 1900 + $yy;
                    } else {
                        $year = (int)$yearS;
                    }

                    $dt = \DateTime::createFromFormat('!Y-m-d', sprintf('%04d-%02d-%02d', $year, $mon, $day));
                    return $dt ? $dt->format('Y-m-d') : null;
                }

                if (preg_match('/^\d{4}$/', $s)) {
                    $year = (int)$s;
                    $dt   = \DateTime::createFromFormat('!Y-m-d', sprintf('%04d-01-01', $year));
                    return $dt ? $dt->format('Y-m-d') : null;
                }

                return null;
            };

            if($oldProject['start']) {

                if($normalizeDate($oldProject['start'])) {
                    $project->setStartDate(new \DateTime($normalizeDate($oldProject['start'])));
                } else {
                    $io->warning(sprintf('Cannot normalize date "%s" of project "%s" (ID: %s)', $oldProject['start'], $oldProject['identifier'], $oldProject['project_id']));
                }

            }

            if($oldProject['end']) {

                if($normalizeDate($oldProject['end'])) {
                    $project->setEndDate(new \DateTime($normalizeDate($oldProject['end'])));
                } else {
                    $io->warning(sprintf('Cannot normalize date "%s" of project "%s" (ID: %s)', $oldProject['end'], $oldProject['identifier'], $oldProject['project_id']));
                }

            }

            $links = [];

            foreach($oldProject2Links as $project2Link) {

                $oldLink = $externalConnection->fetchAssociative('SELECT * FROM zrl_link WHERE link_id = :link_id', [
                    'link_id' => $project2Link['link_id'],
                ]);

                $links[] = [
                    'label' => $oldLink['identifier'],
                    'url' => $oldLink['url'],
                ];

            }

            $project->setLinks($links);

            $states = [];

            foreach($oldProject2Regions as $project2Region) {

                $oldRegion = $externalConnection->fetchAssociative('SELECT * FROM zrl_region WHERE region_id = :region_id', [
                    'region_id' => $project2Region['region_id'],
                ]);

                $state = $this->doctrine->getRepository(State::class)->findOneBy([
                    'name' => $oldRegion['region'],
                ]);

                if($state) {
                    $states[] = $state;
                } else {
                    $io->warning(sprintf('Cannot match state "%s" of project "%s" (ID: %s)', $oldRegion['region'], $oldProject['identifier'], $oldProject['project_id']));
                }

            }

            $project->setStates(new ArrayCollection($states));

            $contacts = [];

            if($oldProject['lead_partner'] || $oldProject['contact'] || $oldProject['function'] || $oldProject['address'] || $oldProject['tel'] || $oldProject['email'] || $oldProject['url']) {

                $contacts[] = [
                    //'name' => $oldProject['lead_partner'] ?? null,
                    //'firstName' => explode(' ', $oldProject['contact'] ?? '', 2)[0] ?? null,
                    //'lastName' => explode(' ', $oldProject['contact'] ?? '', 2)[1] ?? null,
                    //'role' => $oldProject['function'] ?? null,
                    //'phone' => $oldProject['tel'] ?? null,
                    'email' => $oldProject['email'] ?? null,
                    'website' => $oldProject['url'] ?? null,
                    // street zip parsing impossible without AI
                ];

            } else {
                $io->warning(sprintf('No contact provided for project "%s" (ID: %s)', $oldProject['identifier'], $oldProject['project_id']));
            }

            $project->setContacts($contacts);

            if($oldPeriod) {

                $period = $this->doctrine->getRepository(LEPeriod::class)->findOneBy([
                    'name' => $oldPeriod['period'],
                ]);

                if(!$period) {
                    $period = $this->doctrine->getRepository(LEPeriod::class)->findOneBy([
                        'name' => str_replace('–', '-', $oldPeriod['period']),
                    ]);
                }

                if($period) {
                    $project->setLePeriod($period);

                    if($period->getName() === 'LE 07–13') {
                        $project->setIsPublic(false);
                    }
                } else {
                    $io->warning(sprintf('Cannot match period "%s" of project "%s" (ID: %s)', $oldPeriod['period'], $oldProject['identifier'], $oldProject['project_id']));
                }

            }

            foreach(array_slice($oldProject2Leaders, 0, 1) as $oldProject2Leader) {

                $oldLeader = $externalConnection->fetchAssociative('SELECT * FROM zrl_leader WHERE leader_id = :leader_id', [
                    'leader_id' => $oldProject2Leader['leader_id'],
                ]);

                $localWorkgroup = $this->doctrine->getRepository(LocalWorkgroup::class)->findOneBy([
                    'name' => $oldLeader['leader'],
                ]);

                if($localWorkgroup) {
                    $project->setLocalWorkgroup($localWorkgroup);
                } else {
                    $io->warning(sprintf('Cannot match local workgroup "%s" of project "%s" (ID: %s)', $oldLeader['leader'], $oldProject['identifier'], $oldProject['project_id']));
                }

            }

            $project->setLocalWorkgroups(new ArrayCollection([]));

            foreach(array_slice($oldProject2Leaders, 1) as $oldProject2Leader) {

                $oldLeader = $externalConnection->fetchAssociative('SELECT * FROM zrl_leader WHERE leader_id = :leader_id', [
                    'leader_id' => $oldProject2Leader['leader_id'],
                ]);

                $localWorkgroup = $this->doctrine->getRepository(LocalWorkgroup::class)->findOneBy([
                    'name' => $oldLeader['leader'],
                ]);

                if($localWorkgroup) {
                    $project->addLocalWorkgroup($localWorkgroup);
                } else {
                    $io->warning(sprintf('Cannot match local workgroup "%s" of project "%s" (ID: %s)', $oldLeader['leader'], $oldProject['identifier'], $oldProject['project_id']));
                }

            }

            if($oldPlan) {

                $oldPlan['plan'] = str_replace("\xC2\xA0", ' ', $oldPlan['plan']);
                $oldPlan['plan'] = preg_replace('/\s+/u', ' ', $oldPlan['plan']);
                $oldPlan['plan'] = trim($oldPlan['plan']);

                switch($oldPlan['plan']) {
                    case '7.1.1. a) B Pläne und Entwicklungskonzepte zur Erhaltung des natürlichen Erbes - Naturschutz':
                    case '7.1.1. a) L Pläne und Entwicklungskonzepte zur Erhaltung des natürlichen Erbes - Naturschutz':
                        $oldPlan['plan'] = '7.1.1. a) Pläne und Entwicklungskonzepte zur Erhaltung des natürlichen Erbes - Naturschutz';
                        break;
                    case '7.6.1. a) B Studien und Investitionen zur Erhaltung, Wiederherstellung und Verbesserung des natürlichen Erbes - Naturschutz':
                    case '7.6.1. a) L Studien und Investitionen zur Erhaltung, Wiederherstellung und Verbesserung des natürlichen Erbes - Naturschutz':
                        $oldPlan['plan'] = '7.6.1. a) Studien und Investitionen zur Erhaltung, Wiederherstellung und Verbesserung des natürlichen Erbes - Naturschutz';
                        break;
                    case '8.5.1. Investitionen zur Stärkung von Resistenz und ökologischem Wert des Waldes - Öffentlicher Wert & Schutz vor Naturgefahren':
                        $oldPlan['plan'] = '8.5.1. Investitionen zur Stärkung von Resistenz und ökologischem Wert des Waldes';
                        break;
                    case '8.5.3. Investitionen zur Stärkung des ökologischen Werts der Waldökosysteme - Wald-Ökologie-Programm':
                        $oldPlan['plan'] = '8.5.3. Investitionen zur Stärkung des ökologischen Werts der Waldökosysteme';
                        break;
                    case '16.01.1. Unterstützung beim Aufbau & Betrieb operationeller Gruppen der EIP für lw. Produktivität & Nachhaltigkeit':
                        $oldPlan['plan'] = '16.01.1. Unterstützung beim Aufbau & Betrieb operationeller Gruppen der EIP';
                        break;
                    case '16.05.2. a) Stärkung der Zusammenarbeit von AkteurInnen und Strukturen zur Erhaltung des natürlichen Erbes & des Umweltschutzes - Naturschutz':
                        $oldPlan['plan'] = '16.05.2. a) Stärkung der Zusammenarbeit von AkteurInnen und Strukturen zur Erhaltung des natürlichen Erbes & des Umweltschutzes';
                        break;
                    case '16.05.2. b) Stärkung der Zusammenarbeit von AkteurInnen und Strukturen zur Erhaltung des natürlichen Erbes & des Umweltschutzes - Umweltschutz':
                        $oldPlan['plan'] = '16.05.2. b) Stärkung der Zusammenarbeit von AkteurInnen und Strukturen zur Erhaltung des natürlichen Erbes & des Umweltschutzes';
                        break;
                    default:
                        break;
                }

                $leFundingMethod = $this->doctrine->getRepository(LEFundingMethod::class)->findOneBy([
                    'name' => $oldPlan['plan'],
                ]);

                if($leFundingMethod) {

                    $project->setLeFundingMethod($leFundingMethod);

                    if($leFundingMethod->getArticle()) {

                        $project->setLeFundingArticle($leFundingMethod->getArticle());

                        if($leFundingMethod->getArticle()->getCategory()) {
                            $project->setLeFundingCategory($leFundingMethod->getArticle()->getCategory());
                        } else {
                            $io->warning(sprintf('Cannot match category for article "%s" of project "%s" (ID: %s)', $leFundingMethod->getArticle()->getName(), $oldProject['identifier'], $oldProject['project_id']));
                        }

                    } else {
                        $io->warning(sprintf('Cannot match article for plan "%s" of project "%s" (ID: %s)', $oldPlan['plan'], $oldProject['identifier'], $oldProject['project_id']));
                    }

                } else {
                    $missingPlans[$oldPlan['plan']] = ($missingPlans[$oldPlan['plan']] ?? 0) + 1;
                    $io->warning(sprintf('Cannot match plan "%s" of project "%s" (ID: %s)', $oldPlan['plan'], $oldProject['identifier'], $oldProject['project_id']));
                }

            }

            $project->setTopics(new ArrayCollection([]));

            foreach($oldProject2Keywords as $oldProject2Keyword) {

                $oldKeyword = $externalConnection->fetchAssociative('SELECT * FROM zrl_keyword WHERE keyword_id = :keyword_id', [
                    'keyword_id' => $oldProject2Keyword['keyword_id'],
                ]);

                $topicMapping = [
                    'Klimawandelanpassung' => 'Klimawandelanpassung',
                    'Klimaschutz' => 'Klimaschutz',
                    'Erneuerbare Energie' => 'Klimaschutz',
                    'Energieeffizienz' => 'Klimaschutz',
                    'Landwirtschaft' => 'Nachhaltige Land- und Forstwirtschaft',
                    'Forstwirtschaft' => 'Nachhaltige Land- und Forstwirtschaft',
                    'Alm- & Berglandwirtschaft' => 'Nachhaltige Land- und Forstwirtschaft',
                    'Wald' => 'Nachhaltige Land- und Forstwirtschaft',
                    'Boden' => 'Umwelt und Biodiversität',
                    'Tierwohl' => 'Nachhaltige Land- und Forstwirtschaft',
                    'LEADER' => '',
                    'Interkommunale Kooperation' => 'Gemeinwohl, Soziales und Daseinsvorsorge',
                    'Interregionale / Transnationale Kooperationsprojekte' => '',
                    'Lokale Agenda 21' => 'Gemeinwohl, Soziales und Daseinsvorsorge',
                    'Gemeindeentwicklung' => 'Gemeinwohl, Soziales und Daseinsvorsorge',
                    'Standortentwicklung' => 'Ländliche Wirtschaft / KMU',
                    'Chancengleichheit' => 'Gleichstellung',
                    'Frauen' => 'Gleichstellung',
                    'Gender' => 'Gleichstellung',
                    'Jugend' => 'Jugend',
                    'Kultur' => 'Kultur und kulturelles Erbe',
                    'Integration & Soziale Inklusion' => 'Gemeinwohl, Soziales und Daseinsvorsorge',
                    'Soziale Dienstleistungen' => 'Gemeinwohl, Soziales und Daseinsvorsorge',
                    'Gesundheit' => 'Gemeinwohl, Soziales und Daseinsvorsorge',
                    'Nahversorgung' => 'Gemeinwohl, Soziales und Daseinsvorsorge',
                    'Luftreinhaltung' => 'Umwelt und Biodiversität',
                    'Umweltschutz' => 'Umwelt und Biodiversität',
                    'Naturschutz' => 'Naturschutz',
                    'Biodiversität' => 'Umwelt und Biodiversität',
                    'Schutzgebiete' => 'Umwelt und Biodiversität',
                    'ÖPUL' => 'Umwelt und Biodiversität',
                    'Wasser' => 'Umwelt und Biodiversität',
                    'Wertschöpfung' => 'Ländliche Wirtschaft / KMU',
                    'Kurze Versorgungsketten' => 'Ländliche Wirtschaft / KMU',
                    'Diversifizierung' => 'Ländliche Wirtschaft / KMU',
                    'Direktvermarktung' => 'Vermarktung und Vertrieb',
                    'Tourismus' => 'Tourismus',
                    'Betriebswirtschaft' => 'Ländliche Wirtschaft / KMU',
                    'Risikomanagement' => 'Nachhaltige Land- und Forstwirtschaft',
                    'Landwirtschaftliche Dienstleistungen' => 'Ländliche Wirtschaft / KMU',
                    'KMUs, Gewerbe & Wirtschaft' => 'Ländliche Wirtschaft / KMU',
                    'Bildung & Lebenslanges Lernen' => 'Bildung, Sensibilisierung und Wissenstransfer',
                    'Wissenstransfer' => 'Bildung, Sensibilisierung und Wissenstransfer',
                    'Innovation' => '',
                    'EIP Europäische Innovationspartnerschaft' => 'Bildung, Sensibilisierung und Wissenstransfer',
                    'Leerstand' => 'Ländliche Wirtschaft / KMU',
                    'Mobilität' => 'Mobilität',
                    'Neue Finanzierungsformen' => '',
                    'Kulinarik' => 'Lebensmittelverarbeitung und Kulinarik',
                    'Vermarktung und Vertrieb' => 'Vermarktung und Vertrieb',
                    'Lebensmittelverarbeitung' => 'Vermarktung und Vertrieb',
                    'Gastronomie' => 'Gemeinwohl, Soziales und Daseinsvorsorge',
                    'Gemeinschaftsverpflegung' => 'Gemeinwohl, Soziales und Daseinsvorsorge',
                    'Handel' => 'Vermarktung und Vertrieb',
                    'Nachhaltige Landschaftspflege' => 'Nachhaltige Land- und Forstwirtschaft',
                    'Basisdienstleistungen, Leader, Gemeinden' => 'Gemeinwohl, Soziales und Daseinsvorsorge',
                    'EIP-AGRI' => 'Bildung, Sensibilisierung und Wissenstransfer',
                    'Klimaschutz und Klimawandel' => 'Klimaschutz',
                    'Land- und Forstwirtschaft inkl. Wertschöpfungskette' => 'Nachhaltige Land- und Forstwirtschaft',
                    'Umwelt, Biodiversität, Naturschutz' => 'Umwelt und Biodiversität',
                ];

                $topic = $this->doctrine->getRepository(Topic::class)->findOneBy([
                    'name' => $oldKeyword['keyword'],
                ]);

                if(!$topic && ($topicMapping[$oldKeyword['keyword']] ?? null)) {

                    $topic = $this->doctrine->getRepository(Topic::class)->findOneBy([
                        'name' => $topicMapping[$oldKeyword['keyword']],
                    ]);

                }

                if($topic) {
                    $project->addTopic($topic);
                } else {
                    $missingTopics[$oldKeyword['keyword']] = ($missingTopics[$oldKeyword['keyword']] ?? 0) + 1;
                    $io->warning(sprintf('Cannot match topic "%s" of project "%s" (ID: %s)', $oldKeyword['keyword'], $oldProject['identifier'], $oldProject['project_id']));
                }

            }

            $project->setImages([]);

            foreach ($oldProject2Medias as $oldProject2Media) {

                $oldMedia = $externalConnection->fetchAssociative('SELECT * FROM zrl_media WHERE media_id = :media_id', [
                    'media_id' => $oldProject2Media['media_id'],
                ]);

                if($oldMedia['show_web'] === '1') {

                    $sourceDir = __DIR__.'/../../'.rtrim($dirMedia, '/');

                    if(substr(trim($dirMedia), 0, 1) === '/') {
                        $sourceDir = rtrim($dirMedia, '/');
                    }

                    $sourceFile = $sourceDir . '/' . $oldMedia['filename'];

                    if(is_file($sourceFile)) {

                        $mimeType = mime_content_type($sourceFile);
                        $extension = pathinfo($sourceFile, PATHINFO_EXTENSION);

                        $fileData = file_get_contents($sourceFile);
                        $fileHash = md5($fileData);
                        $fileDir = __DIR__.'/../../var/storage/files/'.substr($fileHash, 0, 2);
                        $filePath = $fileDir.'/'.$fileHash.'.'.$extension;

                        if(!is_dir($fileDir) && !$dryRun) {
                            mkdir($fileDir, 0777, true);
                        }

                        if(!is_file($filePath) && !$dryRun) {
                            copy($sourceFile, $filePath);
                        }

                        $file = new File();
                        $file
                            ->setCreatedAt(new \DateTime())
                            ->setExtension($extension)
                            ->setFileHash($fileHash)
                            ->setMimeType($mimeType)
                            ->setName($oldMedia['identifier'])
                            ->setFilePath('var/storage/files/'.substr($fileHash, 0, 2).'/'.$fileHash.'.'.$extension)
                        ;

                        $file = $this->doctrine->getRepository(File::class)->findOneBy(['fileHash' => $fileHash]) ?? $file;

                        if(!$dryRun) {
                            $this->doctrine->getManager()->persist($file);
                            $this->doctrine->getManager()->flush();
                        }

                        $project->setImages([
                            ...$project->getImages(),
                            [
                                'id' => $file->getId(),
                                'name' => $file->getName(),
                                'originalName' => $oldMedia['filename'],
                                'extension' => $extension,
                                'mimeType' => $mimeType,
                                'copyright' => $oldMedia['copyright'],
                                'description' => $oldMedia['identifier'],
                            ],
                        ]);

                    } else {
                        $io->warning(sprintf('Cannot load media file "%s" (ID: %s) of project "%s" (ID: %s)', $sourceFile, $oldMedia['media_id'], $oldProject['identifier'], $oldProject['project_id']));
                    }

                } else {
                    $io->info(sprintf('Media "%s" (ID: %s) of project "%s" (ID: %s) is not public', $oldMedia['identifier'], $oldMedia['media_id'], $oldProject['identifier'], $oldProject['project_id']));
                }

            }

            $project->setFiles([]);

            foreach ($oldProject2Binaries as $oldProject2Binary) {

                $oldBinary = $externalConnection->fetchAssociative('SELECT * FROM zrl_binary WHERE binary_id = :binary_id', [
                    'binary_id' => $oldProject2Binary['binary_id'],
                ]);

                if($oldBinary['show_web'] === '1') {

                    $sourceDir = __DIR__.'/../../'.rtrim($dirBinary, '/');

                    if(substr(trim($dirBinary), 0, 1) === '/') {
                        $sourceDir = rtrim($dirBinary, '/');
                    }

                    $sourceFile = $sourceDir . '/' . $oldBinary['filename'];

                    if(is_file($sourceFile)) {

                        $mimeType = mime_content_type($sourceFile);
                        $extension = pathinfo($sourceFile, PATHINFO_EXTENSION);

                        $fileData = file_get_contents($sourceFile);
                        $fileHash = md5($fileData);
                        $fileDir = __DIR__.'/../../var/storage/files/'.substr($fileHash, 0, 2);
                        $filePath = $fileDir.'/'.$fileHash.'.'.$extension;

                        if(!is_dir($fileDir) && !$dryRun) {
                            mkdir($fileDir, 0777, true);
                        }

                        if(!is_file($filePath) && !$dryRun) {
                            copy($sourceFile, $filePath);
                        }

                        $file = new File();
                        $file
                            ->setCreatedAt(new \DateTime())
                            ->setExtension($extension)
                            ->setFileHash($fileHash)
                            ->setMimeType($mimeType)
                            ->setName($oldBinary['identifier'])
                            ->setFilePath('var/storage/files/'.substr($fileHash, 0, 2).'/'.$fileHash.'.'.$extension)
                        ;

                        $file = $this->doctrine->getRepository(File::class)->findOneBy(['fileHash' => $fileHash]) ?? $file;

                        if(!$dryRun) {
                            $this->doctrine->getManager()->persist($file);
                            $this->doctrine->getManager()->flush();
                        }

                        $project->setFiles([
                            ...$project->getFiles(),
                            [
                                'id' => $file->getId(),
                                'name' => $file->getName(),
                                'originalName' => $oldBinary['filename'],
                                'extension' => $extension,
                                'mimeType' => $mimeType,
                                'copyright' => $oldBinary['copyright'],
                                'description' => $oldBinary['identifier'],
                            ],
                        ]);

                    } else {
                        $io->warning(sprintf('Cannot load binary file "%s" (ID: %s) of project "%s" (ID: %s)', $sourceFile, $oldBinary['media_id'], $oldProject['identifier'], $oldProject['project_id']));
                    }

                } else {
                    $io->info(sprintf('Binary "%s" (ID: %s) of project "%s" (ID: %s) is not public', $oldBinary['identifier'], $oldBinary['media_id'], $oldProject['identifier'], $oldProject['project_id']));
                }

            }

            if(!$dryRun) {
                $this->doctrine->getManager()->persist($project);
                $this->doctrine->getManager()->flush();
            }

            $successCount++;

        }

        $io->info(sprintf('Closing connection to database %s@%s...', $dbname, $dbhost));

        $externalConnection->close();

        if(count($missingPlans)) {

            $io->warning('Folgende Maßnahmen konnten nicht zugeordnet werden:');

            $io->table(
                ['Massnahme', 'Betroffene Projekte'],
                [
                    ...array_map(function ($key) use ($missingPlans) {
                        return [mb_substr($key, 0, 128) ?? '???', $missingPlans[$key]];
                    }, array_keys($missingPlans))
                ]
            );

        }

        if(count($missingTopics)) {

            $io->warning('Folgende Schwerpunkte konnten nicht zugeordnet werden:');

            $io->table(
                ['Schwerpunkt', 'Betroffene Projekte'],
                [
                    ...array_map(function ($key) use ($missingTopics) {
                        return [mb_substr($key, 0, 128) ?? '???', $missingTopics[$key]];
                    }, array_keys($missingTopics))
                ]
            );

        }

        sleep(1);

        $io->success(sprintf('Successfully migrated %s projects :)', $successCount));

        return 0;
    }
}
