<?php

namespace App\Command;

use App\Entity\Project;
use App\Entity\User;
use App\Service\ProjectService;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(name: 'app:projects:update-search-index')]
class ProjectsUpdateSearchIndexCommand extends Command
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
            ->setDescription('Update projects search index')
            ->addOption('limit', null, InputOption::VALUE_OPTIONAL, 'Set limit.', 100)
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $count = 0;
        $limit = $input->getOption('limit');

        /** @var QueryBuilder $qb */
        $qb = $this->doctrine->getManager()->createQueryBuilder();
        $qb
            ->select('p')
            ->from(Project::class, 'p')
            ->andWhere('p.searchIndex IS NULL')
            ->addOrderBy('p.id', 'ASC')
            ->setMaxResults($limit)
        ;

        $projects = $qb->getQuery()->getResult();

        if(!count($projects)) {
            $io->info(sprintf('Search index is up to date'));
            return 0;
        }

        $io->info(sprintf('Updating search index for %s projects...', count($projects)));

        foreach($projects as $project) {

            $project->setSearchIndex($this->projectService->buildSearchIndex($project));
            $this->doctrine->getManager()->persist($project);

            $count++;

        }

        $this->doctrine->getManager()->flush();

        $io->success(sprintf('Successfully updated search index for %s projects :)', $count));

        return 0;
    }
}
