<?php

namespace App\Command;

use App\Entity\Project;
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
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

#[AsCommand(name: 'app:chmos:patch')]
class ChmosPatchCommand extends Command
{
    protected ManagerRegistry $doctrine;
    protected ChmosService $chmosService;
    protected MailerInterface $mailer;
    protected string $mailerFrom;
    protected string $host;

    public function __construct(ManagerRegistry $doctrine, ChmosService $chmosService, MailerInterface $mailer, string $mailerFrom, string $host)
    {
        parent::__construct();
        $this->doctrine = $doctrine;
        $this->chmosService = $chmosService;
        $this->mailer = $mailer;
        $this->mailerFrom = $mailerFrom;
        $this->host = $host;
    }

    protected function configure()
    {
        $this
            ->setDescription('Patch projects with chmos data.')
            ->addOption('ids', null, InputOption::VALUE_OPTIONAL, 'Ids of projects to patch.')
            ->addOption('properties', null, InputOption::VALUE_REQUIRED, 'Comma separated list of properties to patch.')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        /** @var Project[] $projects */
        $qb = $this->doctrine->getManager()->createQueryBuilder();
        $qb
            ->select('p')
            ->from(Project::class, 'p')
        ;

        if($input->getOption('ids'))  {
            $ids = array_map('trim', explode(',', $input->getOption('ids')));
            $qb
                ->where('p.id IN (:ids)')
                ->setParameter('ids', $ids)
            ;
        }

        $projects = $qb->getQuery()->getResult();

        $projects = array_filter($projects, function ($project) {
            return $project->getProjectCode();
        });

        if(!$input->getOption('properties')) {
            $io->error('You must provide at least one property.');
            return 1;
        }

        $properties = array_map('trim', explode(',', $input->getOption('properties')));

        $count = 0;

        foreach($projects as $project) {
            if($this->chmosService->performProjectPatch($project, $properties)) {
                $count++;
            }
        }

        $io->success(sprintf('CHMOS patch completed. %s of %s projects were patched.', $count, count($projects)));

        return 0;
    }
}
