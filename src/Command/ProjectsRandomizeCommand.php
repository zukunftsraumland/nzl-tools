<?php

namespace App\Command;

use App\Entity\Project;
use App\Entity\User;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(name: 'app:projects:randomize')]
class ProjectsRandomizeCommand extends Command
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
            ->setDescription('Randomize projects order')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $count = 0;
        $limit = 100;
        $offset = 0;

        while (true) {

            $io->info(sprintf('Randomize projects from %s to %s...', $offset + 1, $offset + $limit));

            /** @var QueryBuilder $qb */
            $qb = $this->doctrine->getManager()->createQueryBuilder();
            $qb
                ->select('p')
                ->from(Project::class, 'p')
                ->addOrderBy('p.id', 'ASC')
                ->setMaxResults($limit)
                ->setFirstResult($offset)
            ;

            /** @var Project[] $projects */
            $projects = $qb->getQuery()->getResult();

            if(!count($projects)) {
                break;
            }

            foreach($projects as $project) {
                $project->setRandom(rand(0, 1000000));
                $this->doctrine->getManager()->persist($project);
                $count++;
            }

            $this->doctrine->getManager()->flush();

            $offset += $limit;

        }

        $io->success(sprintf('Successfully randomized %s projects :)', $count));

        return 0;
    }
}
