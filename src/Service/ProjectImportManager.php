<?php

namespace App\Service;

use App\Entity\Project;
use App\Entity\ProjectImport;
use App\Entity\ProjectImportItem;
use App\Entity\User;
use App\Entity\LEPeriod;
use App\Service\Importer\AbstractProjectImporter;
use App\Service\Importer\StandardProjectImporter;
use App\Service\Importer\CaseStudyProjectImporter;
use App\Service\Importer\LegacyProjectImporter;
use Doctrine\ORM\EntityManagerInterface;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\String\Slugger\SluggerInterface;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use PhpOffice\PhpSpreadsheet\Writer\IWriter;

/**
 * Manager for handling project imports
 * 
 * This service manages the import of projects from Excel files.
 * It delegates the actual import logic to specific importer classes.
 */
class ProjectImportManager
{
    private EntityManagerInterface $em;
    private ProjectService $projectService;
    private SluggerInterface $slugger;
    private string $uploadDir;
    private array $importers = [];

    public function __construct(
        EntityManagerInterface $em,
        ProjectService $projectService,
        SluggerInterface $slugger,
        string $uploadDir
    ) {
        $this->em = $em;
        $this->projectService = $projectService;
        $this->slugger = $slugger;
        $this->uploadDir = $uploadDir;
        
        // Initialize importers
        $this->initializeImporters();
    }
    
    /**
     * Initialize the available importers
     */
    private function initializeImporters(): void
    {
        // Standard importer
        $standardImporter = new StandardProjectImporter(
            $this->em,
            $this->projectService,
            $this->slugger,
            $this->uploadDir
        );
        $this->importers[$standardImporter->getType()] = $standardImporter;
        
        // Case study importer
        $caseStudyImporter = new CaseStudyProjectImporter(
            $this->em,
            $this->projectService,
            $this->slugger,
            $this->uploadDir
        );
        $this->importers[$caseStudyImporter->getType()] = $caseStudyImporter;
        
        // Legacy importer
        $legacyImporter = new LegacyProjectImporter(
            $this->em,
            $this->projectService,
            $this->slugger,
            $this->uploadDir
        );
        $this->importers[$legacyImporter->getType()] = $legacyImporter;
    }
    
    /**
     * Get all available importers
     * 
     * @return array Array of importers
     */
    public function getImporters(): array
    {
        $result = [];
        
        foreach ($this->importers as $type => $importer) {
            $result[] = [
                'type' => $type,
                'name' => $importer->getName(),
                'description' => $importer->getDescription()
            ];
        }
        
        return $result;
    }
    
    /**
     * Get an importer by type
     * 
     * @param string $type The importer type
     * @return AbstractProjectImporter|null The importer, or null if not found
     */
    public function getImporter(string $type): ?AbstractProjectImporter
    {
        return $this->importers[$type] ?? null;
    }

    /**
     * Create a new import from an uploaded file
     * 
     * @param UploadedFile $file The uploaded file
     * @param User $user The user who initiated the import
     * @param string $importerType The type of importer to use (default: 'standard')
     * @return ProjectImport The created import
     */
    public function createImport(UploadedFile $file, User $user, string $importerType = 'standard'): ProjectImport
    {
        
        try {
            // 1. Prepare the file (check and modify if needed)
            // This returns the path to the original uploaded file or a new temporary file if modified.
            $preparedFilePath = $this->prepareExcelFile($file);
            
            // Use the extension from the *prepared* file, which might have changed if modified/re-saved.
            // Default to original guess if pathinfo fails.
            $extension = pathinfo($preparedFilePath, PATHINFO_EXTENSION) ?: $file->guessExtension() ?: 'xlsx';

            // Generate a unique filename for storage based on the original name
            $originalFilename = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
            $safeFilename = $this->slugger->slug($originalFilename);
            $newFilename = $safeFilename . '-' . uniqid() . '.' . $extension;
            $finalStoragePath = $this->uploadDir . '/' . $newFilename;
            
            // Make sure the upload directory exists
            if (!file_exists($this->uploadDir)) {
                mkdir($this->uploadDir, 0777, true);
            }
            
            // Move the *prepared* file (original or temp modified) to the final uploads directory
            // We use copy and unlink to handle potential cross-filesystem issues with rename/move.
            if (!copy($preparedFilePath, $finalStoragePath)) {
                 // Attempt failed, clean up temp file if it exists
                 if ($preparedFilePath !== $file->getPathname() && file_exists($preparedFilePath)) {
                     @unlink($preparedFilePath);
                 }
                 throw new \Exception('Failed to copy prepared file to upload directory.');
            }
            
            // Delete the temporary file if it was created and successfully copied
            if ($preparedFilePath !== $file->getPathname()) {
                @unlink($preparedFilePath); // Use @ to suppress potential warnings if file is already gone
            }
            
            // Create a new import record
            $import = new ProjectImport();
            $import->setFilename($file->getClientOriginalName());
            $import->setOriginalFilename($file->getClientOriginalName());
            $import->setFilePath($finalStoragePath);
            $import->setStatus(ProjectImport::STATUS_PENDING);
            $import->setUser($user);
            $import->setImporterType($importerType);
            
            // Re-detect importer type based on the potentially modified file content
            // Pass the final storage path for detection
            $detectedImporterType = $this->detectImporterType($import->getFilePath());
            $import->setImporterType($detectedImporterType);
            
            // Get the appropriate importer based on the *detected* type
            $importer = $this->getImporter($detectedImporterType);
            if (!$importer) {
                // If detection led to an invalid type, fail gracefully
                throw new \Exception('Invalid importer type detected: ' . $detectedImporterType);
            }
            
            // Count the total rows in the *final* file
            try {
                $totalRows = $importer->countRows($import->getFilePath());
                $import->setTotalRows($totalRows);
            } catch (\Exception $e) {
                // It's possible the modified file is now unreadable by the row counter
                throw new \Exception('Failed to count rows in prepared Excel file: ' . $e->getMessage());
            }
            
            // Save the import
            $this->em->persist($import);
            $this->em->flush();
            
            return $import;
        } catch (\Exception $e) {
             // Ensure cleanup of temporary file if error occurred at any point before successful copy/unlink
            if (isset($preparedFilePath) && $preparedFilePath !== $file->getPathname() && file_exists($preparedFilePath)) {
                @unlink($preparedFilePath);
            }
            throw $e; // Re-throw the exception
        }
    }
    
    /**
     * Process an import
     * 
     * @param ProjectImport $import The import record
     * @return bool True if the import was processed successfully, false otherwise
     */
    public function processImport(ProjectImport $import): bool
    {
        try {
            // Update import status
            $import->setStatus(ProjectImport::STATUS_PROCESSING);
            $this->em->persist($import);
            $this->em->flush();
            
            // Get the appropriate importer
            $importer = $this->getImporter($import->getImporterType() ?? 'standard');
            if (!$importer) {
                throw new \Exception('Invalid importer type: ' . $import->getImporterType());
            }
            
            // Load the spreadsheet
            $spreadsheet = IOFactory::load($import->getFilePath());
            $worksheet = $spreadsheet->getActiveSheet();
            
            // Get the highest row number
            $highestRow = $worksheet->getHighestRow();
            
            // Skip header rows and start from the first data row
            // For standard import, we have 4 header rows
            $headerRowCount = 4;
            $startRow = $headerRowCount + 1;
            
            // Process each row
            for ($rowIndex = $startRow; $rowIndex <= $highestRow; $rowIndex++) {
                // Create an import item for this row
                $importItem = new ProjectImportItem();
                $importItem->setImport($import);
                $importItem->setRowNumber($rowIndex);
                $importItem->setStatus(ProjectImportItem::STATUS_PENDING);
                
                $this->em->persist($importItem);
            }
            
            // Update import status
            $import->setTotalRows($highestRow - $headerRowCount); // Subtract header rows
            $import->setStatus(ProjectImport::STATUS_PENDING);
            $this->em->persist($import);
            $this->em->flush();
            
            return true;
        } catch (\Exception $e) {
           
            // Update import status
            $import->setStatus(ProjectImport::STATUS_FAILED);
            $import->setErrorMessage('Error: ' . $e->getMessage());
            $this->em->persist($import);
            $this->em->flush();
            
            return false;
        }
    }
    
    /**
     * Process a single import item
     * 
     * @param ProjectImport $import The import record
     * @param int $rowIndex The row index to process
     * @return array The result of processing the row
     */
    public function processImportItem(ProjectImport $import, int $rowIndex): array
    {
        // Get the appropriate importer
        $importer = $this->getImporter($import->getImporterType() ?? 'standard');
        if (!$importer) {
            return [
                'status' => 'error',
                'message' => 'Invalid importer type: ' . $import->getImporterType()
            ];
        }
        
        // Delegate to the importer
        return $importer->processImportItem($import, $rowIndex);
    }
    
    /**
     * Get all imports
     * 
     * @return array Array of imports
     */
    public function getImports(): array
    {
        return $this->em->getRepository(ProjectImport::class)->findBy([], ['createdAt' => 'DESC']);
    }
    
    /**
     * Get a single import by ID
     * 
     * @param int $id The import ID
     * @return ProjectImport|null The import, or null if not found
     */
    public function getImport(int $id): ?ProjectImport
    {
        return $this->em->getRepository(ProjectImport::class)->find($id);
    }
    
    /**
     * Delete an import
     * 
     * @param ProjectImport $import The import to delete
     * @return bool True if the import was deleted successfully, false otherwise
     */
    public function deleteImport(ProjectImport $import): bool
    {
        try {
            // Delete the file
            if (file_exists($import->getFilePath())) {
                unlink($import->getFilePath());
            }
            
            // Delete the import
            $this->em->remove($import);
            $this->em->flush();
            
            return true;
        } catch (\Exception $e) {
            
            return false;
        }
    }
    
    /**
     * Generate a preview of the import data
     * 
     * @param ProjectImport $import The import record
     * @return array The preview data
     */
    public function generatePreview(ProjectImport $import): array
    {
        // Get the appropriate importer
        $importer = $this->getImporter($import->getImporterType() ?? 'standard');
        if (!$importer) {
            throw new \Exception('Invalid importer type: ' . $import->getImporterType());
        }
        
        // Delegate to the importer
        return $importer->generatePreview($import);
    }

    /**
     * Detect importer type based on Excel file content
     * 
     * Examines the Excel file to determine if it's a standard import, case study import, or legacy import.
     * - If column CY contains the value 'Q39.7', it's considered a case study import.
     * - If cell A1 contains the value 'PUBLISHING_DATE', it's considered a legacy import.
     * - Otherwise, it's considered a standard import.
     * 
     * @param string $filePath Path to the Excel file
     * @return string The detected importer type ('standard', 'casestudy', or 'legacy')
     */
    public function detectImporterType(string $filePath): string
    {
        try {
            // Load the Excel file
            $spreadsheet = IOFactory::load($filePath);
            $worksheet = $spreadsheet->getActiveSheet();
            
            // Check for legacy import format (PUBLISHING_DATE in cell A1)
            $a1Value = $worksheet->getCell('A1')->getValue();
            if ($a1Value === 'PUBLISHING_DATE') {
                return 'legacy';
            }
            
            // Get the highest column index
            $highestColumn = $worksheet->getHighestColumn();
            $highestColumnIndex = Coordinate::columnIndexFromString($highestColumn);
            
            // Check for case study marker in column CY
            $markerFound = false;
            
            // Check if column CY exists and contains the value 'Q39.7'
            if ($highestColumnIndex >= Coordinate::columnIndexFromString('CY')) {
                $cyValue = $worksheet->getCell('CY4')->getValue();
                if ($cyValue === 'Q39.7') {
                    $markerFound = true;
                }
            }
            
            if (!$markerFound) {
                $caseStudyHeaders = [
                    'Q21', 'Q22', 'Q23', 'Q24', 'Q25', 'Q26', 'Q27', 
                    'Q28', 'Q29', 'Q30', 'Q31', 'Q32', 'Q33', 'Q34'
                ];
                
                // Scan the header row (row 4) for case study specific headers
                for ($col = 1; $col <= min(120, $highestColumnIndex); $col++) {
                    $headerValue = $worksheet->getCellByColumnAndRow($col, 4)->getValue();
                    
                    if (in_array($headerValue, $caseStudyHeaders)) {
                        $markerFound = true;
                        break;
                    }
                }
            }
            
            // Use the result of our detection
            if ($markerFound) {
                return 'casestudy';
            }
            
            return 'standard';
        } catch (\Exception $e) {
            // Default to standard import if detection fails
            return 'standard';
        }
    }

    /**
     * Import projects from an Excel file
     * 
     * @param ProjectImport $import The import record
     * @param User $user The user who initiated the import
     * @param LEPeriod|null $lePeriod Optional LE Period to assign to all imported projects
     * @param array|null $selectedRows Optional array of row numbers to import (if null, all rows will be imported)
     * @return bool True if the import was successful, false otherwise
     */
    public function importProjects(ProjectImport $import, User $user, ?LEPeriod $lePeriod = null, ?array $selectedRows = null): bool
    {
        // Get the appropriate importer
        $importer = $this->getImporter($import->getImporterType() ?? 'standard');
        if (!$importer) {
            throw new \Exception('Invalid importer type: ' . $import->getImporterType());
        }
        
        // Delegate to the importer
        return $importer->importProjects($import, $user, $lePeriod, $selectedRows);
    }

    /**
     * Prepares the uploaded Excel file. Checks for a specific header in A1
     * and removes columns A-S if found. Saves to a temporary file if modified.
     * Returns the path to the prepared file (original uploaded path or a new temporary file path).
     *
     * @param UploadedFile $file The uploaded Excel file.
     * @return string Path to the prepared file.
     * @throws \Exception If file loading, modification, or saving fails.
     */
    private function prepareExcelFile(UploadedFile $file): string
    {
        $originalFilePath = $file->getPathname();
        $tempFilePath = null; // Path for the modified file, if created

        try {
            // Load the spreadsheet from the uploaded file's temporary location
            $spreadsheet = IOFactory::load($originalFilePath);
            $worksheet = $spreadsheet->getActiveSheet();

            // Check the value in cell A1
            $a1Value = $worksheet->getCell('A1')->getValue();
            $needsModification = ($a1Value === 'Operation System');

            if ($needsModification) {
                
                
                // Define the number of columns to remove (A=1 to S=19 -> 19 columns)
                $columnsToRemove = 19;
                // Remove column by index repeatedly. Removing index 1 shifts others left.
                for ($i = 0; $i < $columnsToRemove; $i++) {
                    // Important check: ensure the worksheet still has columns to remove
                    if ($worksheet->getHighestColumn() >= 'A') {
                         $worksheet->removeColumnByIndex(1); // Remove the first column (index 1)
                    } else {
                        // Should not happen if removing A-S, but safety check
                        
                        break; 
                    }
                }
                

                // Create a temporary file path with the correct extension
                $originalExtension = $file->guessExtension() ?: 'xlsx'; // Fallback extension
                // Ensure tempnam generates a filename in the system's temp dir
                $tempFilePath = tempnam(sys_get_temp_dir(), 'import_prep_');
                // Append the correct extension AFTER getting the base temp name
                 rename($tempFilePath, $tempFilePath .= '.' . $originalExtension);


                // Determine the correct writer based on the extension
                $writerType = match (strtolower($originalExtension)) {
                    'xlsx' => 'Xlsx',
                    'xls' => 'Xls',
                    'ods' => 'Ods',
                    // Add other supported formats if necessary
                    default => 'Xlsx', // Default to Xlsx
                };

                $writer = IOFactory::createWriter($spreadsheet, $writerType);
                $writer->save($tempFilePath);
                

                // Return the path to the temporary modified file
                return $tempFilePath;
            } else {
                // No modification needed, return the original path
                
                return $originalFilePath;
            }

        } catch (\PhpOffice\PhpSpreadsheet\Exception | \Exception $e) {
            // Clean up the temporary file if it was created before the error occurred
            if ($tempFilePath && file_exists($tempFilePath)) {
                @unlink($tempFilePath);
            }
            // Re-throw a more specific exception for clarity
            throw new \Exception('Failed during Excel file preparation: ' . $e->getMessage(), 0, $e);
        }
    }
} 