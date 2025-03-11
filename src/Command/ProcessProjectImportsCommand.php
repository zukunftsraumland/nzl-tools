<?php

namespace App\Command;

use App\Entity\ProjectImport;
use App\Entity\ProjectImportItem;
use App\Service\ProjectImportManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Command to process project imports from Excel files
 * 
 * This command can process:
 * - All pending imports
 * - A specific import by ID
 * - A specific import item by ID
 */
#[AsCommand(name: 'app:process-project-imports')]
class ProcessProjectImportsCommand extends Command
{
    private EntityManagerInterface $entityManager;
    private ProjectImportManager $projectImportManager;

    public function __construct(
        EntityManagerInterface $entityManager, 
        ProjectImportManager $projectImportManager
    ) {
        parent::__construct();
        $this->entityManager = $entityManager;
        $this->projectImportManager = $projectImportManager;
    }

    protected function configure()
    {
        $this
            ->setDescription('Process pending project imports')
            ->setHelp('This command processes project imports from Excel files. It can process all pending imports, a specific import by ID, or a specific import item by ID.')
            ->addOption('import-id', null, InputOption::VALUE_OPTIONAL, 'Process a specific import by ID')
            ->addOption('item-id', null, InputOption::VALUE_OPTIONAL, 'Process a specific import item by ID')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $importId = $input->getOption('import-id');
        $itemId = $input->getOption('item-id');

        try {
            if ($itemId) {
                // Process a specific import item
                $item = $this->entityManager->getRepository(ProjectImportItem::class)->find($itemId);
                
                if (!$item) {
                    $io->error(sprintf('Import item with ID "%s" not found.', $itemId));
                    return Command::FAILURE;
                }
                
                $io->section(sprintf('Processing import item #%d from row %d', $item->getId(), $item->getRowNumber()));
                
                $result = $this->projectImportManager->processImportItem($item->getImport(), $item->getRowNumber());
                
                if ($result['status'] === 'success' || $result['status'] === 'warning') {
                    $io->success(sprintf('Import item #%d processed successfully.', $item->getId()));
                } else {
                    $io->error(sprintf('Import item #%d failed: %s', $item->getId(), $result['message']));
                    return Command::FAILURE;
                }
            } elseif ($importId) {
                // Process a specific import
                $import = $this->entityManager->getRepository(ProjectImport::class)->find($importId);
                
                if (!$import) {
                    $io->error(sprintf('Import with ID "%s" not found.', $importId));
                    return Command::FAILURE;
                }
                
                $io->section(sprintf('Processing import #%d with %d rows', $import->getId(), $import->getTotalRows()));
                
                // First, prepare the import (create import items)
                $result = $this->projectImportManager->processImport($import);
                
                if (!$result) {
                    $io->error(sprintf('Failed to prepare import #%d: %s', $import->getId(), $import->getErrorMessage()));
                    return Command::FAILURE;
                }
                
                // Then process each pending import item
                $items = $this->entityManager->getRepository(ProjectImportItem::class)->findBy([
                    'import' => $import,
                    'status' => ProjectImportItem::STATUS_PENDING
                ]);
                
                $totalItems = count($items);
                $io->progressStart($totalItems);
                
                $successCount = 0;
                $errorCount = 0;
                
                foreach ($items as $item) {
                    $result = $this->projectImportManager->processImportItem($item->getImport(), $item->getRowNumber());
                    
                    if ($result['status'] === 'success' || $result['status'] === 'warning') {
                        $successCount++;
                    } else {
                        $errorCount++;
                    }
                    
                    $io->progressAdvance();
                }
                
                $io->progressFinish();
                
                // Update import status
                if ($errorCount === 0) {
                    $import->setStatus(ProjectImport::STATUS_COMPLETED);
                    $this->entityManager->persist($import);
                    $this->entityManager->flush();
                    
                    $io->success(sprintf('Import #%d completed successfully. %d items processed.', $import->getId(), $successCount));
                } else {
                    $import->setStatus(ProjectImport::STATUS_FAILED);
                    $this->entityManager->persist($import);
                    $this->entityManager->flush();
                    
                    $io->warning(sprintf('Import #%d completed with errors. %d successful, %d failed.', $import->getId(), $successCount, $errorCount));
                }
            } else {
                // Process all pending imports
                $imports = $this->entityManager->getRepository(ProjectImport::class)->findBy([
                    'status' => ProjectImport::STATUS_PENDING
                ]);
                
                if (empty($imports)) {
                    $io->info('No pending imports found.');
                    return Command::SUCCESS;
                }
                
                $io->section(sprintf('Processing %d pending imports', count($imports)));
                
                $totalSuccess = 0;
                $totalFailure = 0;
                
                foreach ($imports as $import) {
                    $io->section(sprintf('Processing import #%d with %d rows', $import->getId(), $import->getTotalRows()));
                    
                    // First, prepare the import (create import items)
                    $result = $this->projectImportManager->processImport($import);
                    
                    if (!$result) {
                        $io->error(sprintf('Failed to prepare import #%d: %s', $import->getId(), $import->getErrorMessage()));
                        $totalFailure++;
                        continue;
                    }
                    
                    // Then process each pending import item
                    $items = $this->entityManager->getRepository(ProjectImportItem::class)->findBy([
                        'import' => $import,
                        'status' => ProjectImportItem::STATUS_PENDING
                    ]);
                    
                    $totalItems = count($items);
                    $io->progressStart($totalItems);
                    
                    $successCount = 0;
                    $errorCount = 0;
                    
                    foreach ($items as $item) {
                        $result = $this->projectImportManager->processImportItem($item->getImport(), $item->getRowNumber());
                        
                        if ($result['status'] === 'success' || $result['status'] === 'warning') {
                            $successCount++;
                        } else {
                            $errorCount++;
                        }
                        
                        $io->progressAdvance();
                    }
                    
                    $io->progressFinish();
                    
                    // Update import status
                    if ($errorCount === 0) {
                        $import->setStatus(ProjectImport::STATUS_COMPLETED);
                        $this->entityManager->persist($import);
                        $this->entityManager->flush();
                        
                        $io->success(sprintf('Import #%d completed successfully. %d items processed.', $import->getId(), $successCount));
                        $totalSuccess++;
                    } else {
                        $import->setStatus(ProjectImport::STATUS_FAILED);
                        $this->entityManager->persist($import);
                        $this->entityManager->flush();
                        
                        $io->warning(sprintf('Import #%d completed with errors. %d successful, %d failed.', $import->getId(), $successCount, $errorCount));
                        $totalFailure++;
                    }
                }
                
                $io->section(sprintf('Processed %d imports: %d successful, %d with errors', count($imports), $totalSuccess, $totalFailure));
            }

            return Command::SUCCESS;
        } catch (\Exception $e) {
            $io->error('An error occurred: ' . $e->getMessage());
            return Command::FAILURE;
        }
    }
} 