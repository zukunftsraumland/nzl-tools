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
            // Generate a unique filename
            $originalFilename = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
            $safeFilename = $this->slugger->slug($originalFilename);
            $newFilename = $safeFilename . '-' . uniqid() . '.' . $file->guessExtension();
            
            // Make sure the upload directory exists
            if (!file_exists($this->uploadDir)) {
                mkdir($this->uploadDir, 0777, true);
            }
            
            // Move the file to the uploads directory
            try {
                $file->move($this->uploadDir, $newFilename);
            } catch (FileException $e) {
                throw new \Exception('Failed to upload file: ' . $e->getMessage());
            }
            
            // Create a new import record
            $import = new ProjectImport();
            $import->setFilename($file->getClientOriginalName());
            $import->setOriginalFilename($file->getClientOriginalName());
            $import->setFilePath($this->uploadDir . '/' . $newFilename);
            $import->setStatus(ProjectImport::STATUS_PENDING);
            $import->setUser($user);
            $import->setImporterType($importerType);
            
            // Get the appropriate importer
            $importer = $this->getImporter($importerType);
            if (!$importer) {
                throw new \Exception('Invalid importer type: ' . $importerType);
            }
            
            // Count the total rows in the file
            try {
                $totalRows = $importer->countRows($import->getFilePath());
                $import->setTotalRows($totalRows);
            } catch (\Exception $e) {
                throw new \Exception('Failed to count rows in Excel file: ' . $e->getMessage());
            }
            
            // Save the import
            $this->em->persist($import);
            $this->em->flush();
            
            return $import;
        } catch (\Exception $e) {
            throw $e;
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
     * Import projects from an Excel file
     * 
     * @param ProjectImport $import The import record
     * @param User $user The user who initiated the import
     * @param LEPeriod|null $lePeriod Optional LE Period to assign to all imported projects
     * @return bool True if the import was successful, false otherwise
     */
    public function importProjects(ProjectImport $import, User $user, ?LEPeriod $lePeriod = null): bool
    {
        // Get the appropriate importer
        $importer = $this->getImporter($import->getImporterType() ?? 'standard');
        if (!$importer) {
            throw new \Exception('Invalid importer type: ' . $import->getImporterType());
        }
        
        // Delegate to the importer
        return $importer->importProjects($import, $user, $lePeriod);
    }
} 