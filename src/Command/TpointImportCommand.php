<?php

namespace App\Command;

use App\Entity\City;
use App\Service\TpointService;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(name: 'app:tpoint:import')]
class TpointImportCommand extends Command
{
    protected ManagerRegistry $doctrine;
    protected TpointService $tpointService;

    public function __construct(ManagerRegistry $doctrine, TpointService $tpointService)
    {
        parent::__construct();
        $this->doctrine = $doctrine;
        $this->tpointService = $tpointService;
    }

    protected function configure()
    {
        $this
            ->setDescription('Import tpoint data')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $result = $this->tpointService->updateContacts();

        $io->success(sprintf('Successfully updated %s contacts :)', count($result['updated'])));
        $io->success(sprintf('Successfully created %s contacts :)', count($result['created'])));

        return 0;
    }
}
