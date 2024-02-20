<?php

namespace App\Command;

use App\Entity\City;
use App\Entity\Region;
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

#[AsCommand(name: 'app:import:regions')]
class ImportRegionsCommand extends Command
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
            ->setDescription('Import regions')
            ->addArgument('xlsx', InputArgument::REQUIRED, 'XLSX to import')
            ->addOption('skip', null, InputOption::VALUE_OPTIONAL, 'Skip rows', 1)
            ->addOption('municipal-number-column', null, InputOption::VALUE_OPTIONAL, 'Column of the municipal number', 'A')
            ->addOption('state-column', null, InputOption::VALUE_OPTIONAL, 'Column of the state code', 'B')
            ->addOption('name-columns', null, InputOption::VALUE_IS_ARRAY | InputOption::VALUE_OPTIONAL, 'Columns of the regions', ['C', 'D'])
            ->addOption('type-mapping', null, InputOption::VALUE_IS_ARRAY | InputOption::VALUE_OPTIONAL, 'Types of the regions')
            ->addOption('remove-orphans', null, InputOption::VALUE_NONE, 'Delete orphaned regions')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($input->getArgument('xlsx'));

        $regions = [];
        $created = 0;
        $updated = 0;
        $deleted = 0;

        foreach($input->getOption('name-columns') as $key => $nameColumn) {

            $row = 1 + $input->getOption('skip');

            while (true) {

                $municipalNumber = $spreadsheet
                    ->getActiveSheet()
                    ->getCell($input->getOption('municipal-number-column').$row)
                    ->getCalculatedValue();

                $name = $spreadsheet
                    ->getActiveSheet()
                    ->getCell($nameColumn.$row)
                    ->getCalculatedValue();

                if(!$municipalNumber) {
                    break;
                }

                if($name === 1) {
                    $spreadsheet
                        ->getActiveSheet()
                        ->setCellValue(
                            $nameColumn.$row,
                            $spreadsheet
                                ->getActiveSheet()
                                ->getCell($nameColumn.'1')
                                ->getCalculatedValue()
                        )
                    ;

                    $name = $spreadsheet
                        ->getActiveSheet()
                        ->getCell($nameColumn.$row)
                        ->getCalculatedValue();
                }

                if($nameColumn === $input->getOption('state-column')) {

                    $state = $this->doctrine->getRepository(State::class)->findOneBy([
                        'code' => $name,
                    ]);

                    if($state) {
                        $spreadsheet
                            ->getActiveSheet()
                            ->setCellValue(
                                $input->getOption('state-column').$row,
                                $state->getName(),
                            );

                        $name = $state->getName();
                    }

                }

                $row++;

                if(!$name) {
                    continue;
                }

                foreach(array_map('trim', explode(',', trim($name))) as $region) {
                    $regions[] = [
                        'type' => isset($input->getOption('type-mapping')[$key]) ? $input->getOption('type-mapping')[$key] : 'unknown',
                        'name' => $region,
                    ];
                }

            }

        }

        $regions = array_unique($regions, SORT_REGULAR);

        foreach($regions as $region) {

            $type = $region['type'];
            $name = $region['name'];

            $region = $this->doctrine->getManager()->getRepository(Region::class)->findOneBy([
                'name' => $name,
                'type' => $type,
            ]);

            if(!$region) {
                $region = new Region();
                $region->setCreatedAt(new \DateTime());
            }

            $region
                ->setUpdatedAt(new \DateTime())
                ->setName($name)
                ->setType($type)
                ->setIsPublic(true)
                ->setCities([])
                ->setTranslations([
                    'fr' => [
                        'name' => null,
                    ],
                    'it' => [
                        'name' => null,
                    ],
                ])
            ;

            if($region->getId()) {
                $updated++;
            } else {
                $created++;
            }

            $this->doctrine->getManager()->persist($region);
            $this->doctrine->getManager()->flush();

        }

        if($input->getOption('remove-orphans')) {

            $persistedRegions = $this->doctrine->getManager()->getRepository(Region::class)->findAll();

            foreach($persistedRegions as $persistedRegion) {

                foreach($regions as $region) {
                    if($persistedRegion->getType() === $region['type'] && $persistedRegion->getName() === $region['name']) {
                        continue 2;
                    }
                }

                $deleted++;

                $this->doctrine->getManager()->remove($persistedRegion);

            }

            if($deleted) {
                $this->doctrine->getManager()->flush();
                $io->warning(sprintf('Successfully deleted %s regions.', $deleted));
            }

        }

        $io->success(sprintf('Successfully updated %s regions :)', $updated));
        $io->success(sprintf('Successfully created %s regions :)', $created));

        $row = 1 + $input->getOption('skip');
        $count = 0;

        while (true) {

            $municipalNumber = $spreadsheet
                ->getActiveSheet()
                ->getCell($input->getOption('municipal-number-column').$row)
                ->getCalculatedValue();

            if(!$municipalNumber) {
                break;
            }

            $city = $this->doctrine->getManager()->getRepository(City::class)->findOneBy([
                'municipalNumber' => $municipalNumber,
            ]);

            if(!$city) {
                $io->warning(sprintf('No city found for municipal number "%s"', $municipalNumber));
                break;
            }

            foreach($input->getOption('name-columns') as $key => $nameColumn) {

                $regionName = $spreadsheet
                    ->getActiveSheet()
                    ->getCell($nameColumn.$row)
                    ->getCalculatedValue();

                if(!$regionName) {
                    continue;
                }

                foreach(array_map('trim', explode(',', trim($regionName))) as $regionName) {

                    $region = $this->doctrine->getManager()->getRepository(Region::class)->findOneBy([
                        'name' => $regionName,
                        'type' => isset($input->getOption('type-mapping')[$key]) ? $input->getOption('type-mapping')[$key] : 'unknown',
                    ]);

                    if(!$region) {
                        $io->warning(sprintf('No region found with name "%s"', $regionName));
                        continue;
                    }

                    $region->addCity($city);

                    $this->doctrine->getManager()->persist($region);
                    $this->doctrine->getManager()->flush();

                    $count++;

                }

            }

            $row++;

        }

        $io->success(sprintf('Successfully connected %s cities :)', $count));

        return 0;
    }
}
