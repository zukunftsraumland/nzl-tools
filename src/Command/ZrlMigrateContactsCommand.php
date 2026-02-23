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

#[AsCommand(name: 'app:zrl:migrate-contacts')]
class ZrlMigrateContactsCommand extends Command
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
        $dryRun = $input->getOption('dry-run');

        $skipCount = 0;
        $successCount = 0;

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

            $oldProjectTrans = $externalConnection->fetchAssociative('SELECT * FROM zrl_project_trans WHERE project_id = :project_id', [
                'project_id' => $oldProject['project_id'],
                'language_id' => 'de',
            ]);

            $project = $this->doctrine->getManager()->getRepository(Project::class)->findOneBy([
                'source' => 'zrl',
                'foreignId' => $oldProject['project_id'],
            ]);

            $project = $this->doctrine->getManager()->getRepository(Project::class)->findOneBy([
                'source' => 'legacy_import',
                'title' => $oldProjectTrans['title'],
            ]) ?? $project;

            if(!$project || !$project->getId()) {
                $io->info(sprintf('No matching project found for "%s" (ID: %s)...', $oldProjectTrans['title'], $oldProject['project_id']));
                $skipCount++;
                continue;
            }

            $contacts = [];

            if($oldProject['lead_partner'] || $oldProject['contact'] || $oldProject['function'] || $oldProject['address'] || $oldProject['tel'] || $oldProject['email'] || $oldProject['url']) {

                $contacts[] = [
                    'name' => $oldProject['lead_partner'] ?? null,
                    'firstName' => explode(' ', $oldProject['contact'] ?? '', 2)[0] ?? null,
                    'lastName' => explode(' ', $oldProject['contact'] ?? '', 2)[1] ?? null,
                    'role' => $oldProject['function'] ?? null,
                    'phone' => $oldProject['tel'] ?? null,
                    'email' => $oldProject['email'] ?? null,
                    'website' => $oldProject['url'] ?? null,
                    // street zip parsing impossible without AI
                ];

            } else {
                $io->warning(sprintf('No contact provided for project "%s" (ID: %s)', $oldProject['identifier'], $oldProject['project_id']));
                $skipCount++;
                continue;
            }

            $project->setUpdatedAt(new \DateTime());
            $project->setContacts($contacts);

            if(!$dryRun) {
                $this->doctrine->getManager()->persist($project);
                $this->doctrine->getManager()->flush();
            }

            $successCount++;

        }

        $io->info(sprintf('Closing connection to database %s@%s...', $dbname, $dbhost));

        $externalConnection->close();

        if($skipCount) {
            $io->success(sprintf('Could not migrate contacts for %s projects.', $skipCount));
        }

        sleep(1);

        $io->success(sprintf('Successfully migrated %s contacts :)', $successCount));

        return 0;
    }
}
