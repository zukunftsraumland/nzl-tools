<?php

namespace App\Command;

use App\Entity\City;
use App\Entity\State;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(name: 'app:import:cities')]
class ImportCitiesCommand extends Command
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
            ->setDescription('Import cities')
            ->addArgument('xlsx', InputArgument::REQUIRED, 'XLSX to import')
            ->addOption('skip', null, InputOption::VALUE_OPTIONAL, 'Skip rows', 1)
            ->addOption('municipal-number-column', null, InputOption::VALUE_OPTIONAL, 'Column of the municipal number', 'A')
            ->addOption('name-column', null, InputOption::VALUE_OPTIONAL, 'Column of the name', 'B')
            ->addOption('state-column', null, InputOption::VALUE_OPTIONAL, 'Column of the state code', 'C')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($input->getArgument('xlsx'));

        $row = 1 + $input->getOption('skip');
        $created = 0;
        $updated = 0;

        while (true) {

            $municipalNumber = $spreadsheet
                ->getActiveSheet()
                ->getCell($input->getOption('municipal-number-column').$row)
                ->getCalculatedValue();
            $name = $spreadsheet
                ->getActiveSheet()
                ->getCell($input->getOption('name-column').$row)
                ->getCalculatedValue();
            $state = $spreadsheet
                ->getActiveSheet()
                ->getCell($input->getOption('state-column').$row)
                ->getCalculatedValue();

            if(!$municipalNumber && !$name) {
                break;
            }

            if($state) {
                $state = $this->doctrine->getManager()->getRepository(State::class)->findOneBy([
                    'code' => $state,
                ]);
            }

            $city = $this->doctrine->getManager()->getRepository(City::class)->findOneBy([
                'municipalNumber' => $municipalNumber,
            ]);

            if(!$city) {
                $city = new City();
                $city->setCreatedAt(new \DateTime());
            }

            $city
                ->setUpdatedAt(new \DateTime())
                ->setName($name)
                ->setMunicipalNumber($municipalNumber)
                ->setIsPublic(true)
            ;

            if($state) {
                $city->setState($state);
            }

            if($city->getId()) {
                $updated++;
            } else {
                $created++;
            }

            $this->doctrine->getManager()->persist($city);

            $row++;

        }

        $this->doctrine->getManager()->flush();

        $io->success(sprintf('Successfully updated %s cities :)', $updated));
        $io->success(sprintf('Successfully created %s cities :)', $created));

        return 0;
    }
}
