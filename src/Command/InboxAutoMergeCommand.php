<?php

namespace App\Command;

use App\Entity\Inbox;
use App\Entity\Project;
use App\Entity\Topic;
use App\Entity\User;
use App\Service\ChmosService;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

#[AsCommand(name: 'app:inbox:auto-merge')]
class InboxAutoMergeCommand extends Command
{
    protected ManagerRegistry $doctrine;

    public function __construct(ManagerRegistry $doctrine)
    {
        parent::__construct();
        $this->doctrine = $doctrine;
    }

    protected function configure()
    {
        $this
            ->setName('app:inbox:auto-merge')
            ->setDescription('Auto merge inbox entries with no changes.')
            ->addOption('merge-topics', null, InputOption::VALUE_NONE, 'Use new topics.')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $count = 0;

        /** @var Inbox[] $inbox */
        $inbox = $this->doctrine->getManager()->getRepository(Inbox::class)->findBy([
            'type' => 'project',
            'source' => 'chmos',
            'isMerged' => false,
            'status' => 'update',
        ]);

        /** @var Inbox[] $mergedInbox */
        $mergedInbox = $this->doctrine->getManager()->getRepository(Inbox::class)->findBy([
            'type' => 'project',
            'source' => 'chmos',
            'isMerged' => true,
        ]);

        $io->info(sprintf('Checking %s new inbox entries against %s merged entries.', count($inbox), count($mergedInbox)));

        foreach($inbox as $inboxItem) {

            $alreadyMerged = false;

            foreach($mergedInbox as $mergedInboxItem) {

                if(json_encode($mergedInboxItem->getNormalizedData()) === json_encode($inboxItem->getNormalizedData()) || json_encode($mergedInboxItem->getData()) === json_encode($inboxItem->getData())) {
                    $alreadyMerged = true;
                    break;
                }

                if(json_encode(array_filter(array_values($mergedInboxItem->getNormalizedData()))) === json_encode(array_filter(array_values($inboxItem->getNormalizedData())))
                    || json_encode(array_filter(array_values($mergedInboxItem->getData()))) === json_encode(array_filter(array_values($inboxItem->getData())))) {
                    $alreadyMerged = true;
                    break;
                }

                $aNormalizedData = array_merge([], $mergedInboxItem->getNormalizedData());
                $aNormalizedData['states'] = isset($aNormalizedData['states']) ? $aNormalizedData['states'] : [];
                $aNormalizedData['states'] = array_filter($aNormalizedData['states'], function ($a) {
                    return count(array_filter($a, function ($aa) {
                        return isset($aa['id']) && $aa['id'];
                    })) > 0;
                });

                $bNormalizedData = array_merge([], $inboxItem->getNormalizedData());
                $bNormalizedData['states'] = isset($bNormalizedData['states']) ? $bNormalizedData['states'] : [];
                $bNormalizedData['states'] = array_filter($bNormalizedData['states'], function ($b) {
                    return count(array_filter($b, function ($bb) {
                        return isset($bb['id']) && $bb['id'];
                    })) > 0;
                });

                $aNormalizedData = array_merge([], $mergedInboxItem->getNormalizedData());
                $aNormalizedData['topics'] = isset($aNormalizedData['topics']) ? $aNormalizedData['topics'] : [];
                $aNormalizedData['topics'] = array_filter($aNormalizedData['topics'], function ($a) {
                    return count(array_filter($a, function ($aa) {
                        return isset($aa['id']) && $aa['id'];
                    })) > 0;
                });

                $bNormalizedData = array_merge([], $inboxItem->getNormalizedData());
                $bNormalizedData['topics'] = isset($bNormalizedData['topics']) ? $bNormalizedData['topics'] : [];
                $bNormalizedData['topics'] = array_filter($bNormalizedData['topics'], function ($b) {
                    return count(array_filter($b, function ($bb) {
                        return isset($bb['id']) && $bb['id'];
                    })) > 0;
                });

                if(json_encode($aNormalizedData) === json_encode($bNormalizedData)) {
                    $alreadyMerged = true;
                    break;
                }

            }

            if($alreadyMerged) {
                $io->info(sprintf('Inbox entry %s was already merged. Auto merging...', $inboxItem->getId()));
                $inboxItem->setIsMerged(true);
                $inboxItem->setMergedAt(new \DateTime());
                $this->doctrine->getManager()->persist($inboxItem);
                $this->doctrine->getManager()->flush();
                $count++;
            }

            if($alreadyMerged && $input->getOption('merge-topics')) {

                /** @var Project $project */
                $project = $this->doctrine->getManager()->getRepository(Project::class)->findOneBy([
                    'id' => $inboxItem->getInternalId(),
                ]);

                if($project) {

                    foreach($inboxItem->getNormalizedData()['topics'] as $topic) {

                        if(array_key_exists('id', $topic) && $topic['id']) {

                            /** @var Topic $topic */
                            $topic = $this->doctrine->getManager()->getRepository(Topic::class)->find($topic['id']);

                            $project->addTopic($topic);

                            $io->info(sprintf('Adding topic %s for project %s.', $topic->getName(), $project->getId()));

                        }

                        $this->doctrine->getManager()->persist($project);
                        $this->doctrine->getManager()->flush();

                    }

                } else {
                    $io->error(sprintf('Could not update topics for entry %s.', $inboxItem->getId()));
                }

            }

        }

        $io->success(sprintf('%s inbox entries were auto merged.', $count));

        return 0;
    }
}
