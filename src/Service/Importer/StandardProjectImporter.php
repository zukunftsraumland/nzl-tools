<?php

namespace App\Service\Importer;

use App\Entity\ProjectImport;
use App\Entity\ProjectImportItem;
use App\Entity\User;
use App\Entity\LEPeriod;
use App\Entity\Tag;
use App\Entity\LEFundingCategory;
use App\Entity\LocalWorkgroup;
use App\Service\ProjectService;
use Doctrine\ORM\EntityManagerInterface;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use Symfony\Component\String\Slugger\SluggerInterface;

/**
 * Standard Project Importer
 * 
 * Handles importing projects from Excel files in the standard format (Q2.1 fields).
 * Processes project data, attachments, contacts, and related entities.
 * Supports preview generation and full import with validation.
 */
class StandardProjectImporter extends AbstractProjectImporter
{
    protected ProjectService $projectService;

    public function __construct(
        EntityManagerInterface $em,
        ProjectService $projectService,
        SluggerInterface $slugger,
        string $uploadDir
    ) {
        parent::__construct($em, $slugger, $uploadDir);
        $this->projectService = $projectService;
    }

    /**
     * Returns the importer name for UI display
     */
    public function getName(): string
    {
        return 'Standard';
    }

    /**
     * Returns the importer description for UI display
     */
    public function getDescription(): string
    {
        return 'Import von Projekten im Standardformat (Q2.1, etc.)';
    }

    /**
     * Returns the importer type identifier
     */
    public function getType(): string
    {
        return 'standard';
    }

    /**
     * Returns the number of header rows in the Excel file
     */
    protected function getHeaderRowCount(): int
    {
        return 4;
    }

    /**
     * Process a single row from the Excel import file
     * 
     * @param ProjectImport $import The import object
     * @param int $rowIndex The row index to process (0-based, excluding headers)
     * @return array Result with status, payload, and any error messages
     */
    public function processImportItem(ProjectImport $import, int $rowIndex): array
    {
        try {
            $filePath = $import->getFilePath();
            if (!file_exists($filePath)) {
                $filePath = $this->uploadDir . '/' . $filePath;
            }
            
            $spreadsheet = IOFactory::load($filePath);
            $worksheet = $spreadsheet->getActiveSheet();
            $highestColumnIndex = Coordinate::columnIndexFromString($worksheet->getHighestColumn());
            
            $headerMapping = [];
            $parentHeaders = [];
            
            // Map parent headers from row 1
            for ($col = 1; $col <= $highestColumnIndex; $col++) {
                $parentHeader = $worksheet->getCellByColumnAndRow($col, 1)->getValue();
                if (!empty($parentHeader)) {
                    $parentHeaders[$col] = $parentHeader;
                }
            }
            
            // Map field names from row 4 with their parent headers
            for ($col = 1; $col <= $highestColumnIndex; $col++) {
                $fieldName = $worksheet->getCellByColumnAndRow($col, 4)->getValue();
                if (!empty($fieldName)) {
                    $currentParent = null;
                    foreach ($parentHeaders as $parentCol => $parentValue) {
                        if ($parentCol <= $col) {
                            $currentParent = $parentValue;
                        }
                    }
                    $headerMapping[$col] = [
                        'parent' => $currentParent,
                        'field' => $fieldName
                    ];
                }
            }
            
            $actualRowIndex = $rowIndex + $this->getHeaderRowCount();
            $rowData = [];
            
            // Extract data from the row using both field names and column letters
            for ($col = 1; $col <= $highestColumnIndex; $col++) {
                $cellValue = $worksheet->getCellByColumnAndRow($col, $actualRowIndex)->getValue();
                if ($cellValue !== null) {
                    if (isset($headerMapping[$col])) {
                        $rowData[$headerMapping[$col]['field']] = $cellValue;
                    }
                    $rowData[Coordinate::stringFromColumnIndex($col)] = $cellValue;
                }
            }
            
            if (empty($rowData)) {
                return [
                    'rowNumber' => $rowIndex,
                    'title' => '',
                    'description' => '',
                    'projectCode' => '',
                    'startDate' => null,
                    'endDate' => null,
                    'status' => 'error',
                    'message' => 'Empty row',
                    'payload' => []
                ];
            }
            
            $payload = $this->prepareProjectPayload($rowData);
            
            // Add category and workgroup names if IDs are available
            if (isset($payload['leFundingCategoryId']) && is_numeric($payload['leFundingCategoryId'])) {
                $categoryId = (int)$payload['leFundingCategoryId'];
                $categoryMapping = $this->getLeCategoryNameMapping();
                if (isset($categoryMapping[$categoryId])) {
                    $payload['leFundingCategoryName'] = $categoryMapping[$categoryId];
                }
            }
            
            if (isset($payload['localWorkgroupId']) && is_numeric($payload['localWorkgroupId'])) {
                $workgroupId = (int)$payload['localWorkgroupId'];
                $workgroupMapping = $this->getLocalWorkgroupNameMapping();
                if (isset($workgroupMapping[$workgroupId])) {
                    $payload['localWorkgroupName'] = $workgroupMapping[$workgroupId];
                }
            }
            
            $result = [
                'rowNumber' => $rowIndex,
                'title' => $payload['title'] ?? 'Untitled',
                'description' => $payload['description'] ?? '',
                'projectCode' => $payload['projectCode'] ?? '',
                'startDate' => $payload['startDate'] ?? null,
                'endDate' => $payload['endDate'] ?? null,
                'status' => 'valid',
                'message' => '',
                'payload' => $payload
            ];
            
            if (empty($result['title'])) {
                $result['status'] = 'error';
                $result['message'] = 'Project title (Q2.1) is required';
            } elseif ($this->findProjectByTitle($result['title'])) {
                $result['status'] = 'warning';
                $result['message'] = 'Projekt mit diesem Namen existiert bereits';
            }
            
            return $result;
        } catch (\Exception $e) {
            return [
                'rowNumber' => $rowIndex,
                'title' => '',
                'description' => '',
                'projectCode' => '',
                'startDate' => null,
                'endDate' => null,
                'status' => 'error',
                'message' => 'Error: ' . $e->getMessage(),
                'payload' => []
            ];
        }
    }

    /**
     * Prepare project data from Excel row for import
     * 
     * Processes raw Excel data into a structured payload for project creation.
     * Handles basic fields, dates, contacts, files, and related entities.
     * 
     * @param array $data Raw data from Excel row
     * @return array Processed project payload
     */
    protected function prepareProjectPayload(array $data): array
    {
        $payload = [
            'isPublic' => false,
            'projectCode' => $data['Q2.2'] ?? $data['B'] ?? '',
            'title' => $data['Q2.1'] ?? $data['A'] ?? '',
            'keywords' => $data['Q4'] ?? $data['U'] ?? '',
            'description' => $data['Q11'] ?? $data['AN'] ?? '',
            'projectCosts' => $data['Q9'] ?? $data['AL'] ?? null,
            'startDate' => null,
            'endDate' => null,
            'cooperationProjectAt' => isset($data['Q8.1']) ? (bool)$data['Q8.1'] : false,
            'cooperationProjectEu' => isset($data['Q8.2']) ? (bool)$data['Q8.2'] : false,
            'topics' => [],
            'tags' => [],
            'geographicRegions' => [],
            'countries' => [],
            'contacts' => [],
            'files' => [],
            'images' => []
        ];

        // Process dates
        foreach ([
            'startDate' => ['Q2.3', 'C'],
            'endDate' => ['Q2.4', 'D']
        ] as $field => $keys) {
            foreach ($keys as $key) {
                if (!empty($data[$key])) {
                    try {
                        $value = $data[$key];
                        if ($value instanceof \DateTime) {
                            $payload[$field] = $value->format('Y-m-d');
                        } elseif (is_numeric($value)) {
                            $payload[$field] = \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($value)->format('Y-m-d');
                        } else {
                            $payload[$field] = (new \DateTime($value))->format('Y-m-d');
                        }
                        break;
                    } catch (\Exception $e) {
                        continue;
                    }
                }
            }
        }

        // Process LE funding category
        if (!empty($data['Q6']) || !empty($data['AF'])) {
            $excelCategoryId = $data['Q6'] ?? $data['AF'] ?? null;
            if (is_numeric($excelCategoryId)) {
                $categoryId = (int)$excelCategoryId;
                $categoryMapping = $this->getLeCategoryMapping();
                if (isset($categoryMapping[$categoryId])) {
                    $payload['leFundingCategoryId'] = $categoryMapping[$categoryId];
                }
            }
        }

        // Process local workgroup
        if (!empty($data['Q7']) || !empty($data['AG'])) {
            $excelWorkgroupId = $data['Q7'] ?? $data['AG'] ?? null;
            if (is_numeric($excelWorkgroupId)) {
                $workgroupId = (int)$excelWorkgroupId;
                $workgroupMapping = $this->getLocalWorkgroupMapping();
                if (isset($workgroupMapping[$workgroupId])) {
                    $payload['localWorkgroupId'] = $workgroupMapping[$workgroupId];
                }
            }
        }

        // Process tags
        if (!empty($data['Q4']) || !empty($data['U'])) {
            $keywords = $data['Q4'] ?? $data['U'] ?? '';
            foreach (array_filter(array_map('trim', explode(',', $keywords))) as $keyword) {
                $payload['tags'][] = [
                    'name' => $keyword,
                    'context' => 'tag'
                ];
            }
        }

        $this->processContactsFromExcel($data, $payload);
        $this->processFileAttachmentsFromExcel($data, $payload);

        return $payload;
    }

    /**
     * Process contact information from Excel columns AO-AX
     * 
     * AO: First and last name (split by first space)
     * AP: Email
     * AQ: Organization name
     * AR: Second contact first and last name (if filled)
     * AS: Second contact email (if filled)
     * AT: Phone number
     * AU: Zipcode
     * AV: City
     * AW: Street
     * AX: Ignored
     * 
     * @param array $data The raw data from Excel
     * @param array &$payload The project payload to update
     */
    private function processContactsFromExcel(array $data, array &$payload): void
    {
        // Process first contact if name exists
        if (!empty($data['AO'])) {
            $nameParts = explode(' ', trim($data['AO']), 2);
            $firstName = $nameParts[0] ?? '';
            $lastName = $nameParts[1] ?? '';
            
            $contact = [
                'firstName' => $firstName,
                'lastName' => $lastName,
                'email' => $data['AP'] ?? '',
                'name' => $data['AQ'] ?? '', // Organization name
                'phone' => $data['AT'] ?? '',
                'zipCode' => $data['AU'] ?? '',
                'city' => $data['AV'] ?? '',
                'street' => $data['AW'] ?? '',
                'public' => true
            ];
            
            $payload['contacts'][] = $contact;
        }
        
        // Process second contact if name exists
        if (!empty($data['AR'])) {
            $nameParts = explode(' ', trim($data['AR']), 2);
            $firstName = $nameParts[0] ?? '';
            $lastName = $nameParts[1] ?? '';
            
            $contact = [
                'firstName' => $firstName,
                'lastName' => $lastName,
                'email' => $data['AS'] ?? '',
                'name' => $data['AQ'] ?? '', // Use same organization as first contact
                'phone' => $data['AT'] ?? '',
                'zipCode' => $data['AU'] ?? '',
                'city' => $data['AV'] ?? '',
                'street' => $data['AW'] ?? '',
                'public' => true
            ];
            
            $payload['contacts'][] = $contact;
        }
    }

    /**
     * Process file attachments from Excel columns BO to CR
     * Columns are organized in pairs: filename followed by URL
     * 
     * @param array $data The Excel data
     * @param array &$payload The project payload to update
     */
    private function processFileAttachmentsFromExcel(array $data, array &$payload): void
    {
        $filePairs = [
            ['BO', 'BP'], ['BQ', 'BR'], ['BS', 'BT'], ['BU', 'BV'], ['BW', 'BX'],
            ['BY', 'BZ'], ['CA', 'CB'], ['CC', 'CD'], ['CE', 'CF'], ['CG', 'CH'],
            ['CI', 'CJ'], ['CK', 'CL'], ['CM', 'CN'], ['CO', 'CP'], ['CQ', 'CR'],
            ['CS', 'CT'], ['CU', 'CV'], ['CW', 'CX'], ['CY', 'CZ']
        ];
        
        $columnWithPictureCopyRightText = 'DA';
        $existingImageIds = [];
        $existingFileIds = [];
        
        // Populate existing IDs from payload
        if (isset($payload['images']) && is_array($payload['images'])) {
            $existingImageIds = array_column($payload['images'], 'id');
        }
        
        if (isset($payload['files']) && is_array($payload['files'])) {
            $existingFileIds = array_column($payload['files'], 'id');
        }
        
        foreach ($filePairs as $pair) {
            $filenameCol = $pair[0];
            $urlCol = $pair[1];
            
            if (empty($data[$filenameCol]) || empty($data[$urlCol])) {
                continue;
            }
            
            $filename = $data[$filenameCol];
            $url = $data[$urlCol];
            
            try {
                $fileData = $this->downloadFileFromUrl($url, $filename);
                if (!$fileData) {
                    continue;
                }
                
                $isImage = $this->isImageFile($filename);
                
                if ($isImage && !in_array($fileData['id'], $existingImageIds)) {
                    $payload['images'][] = [
                        'id' => $fileData['id'],
                        'name' => $fileData['name'],
                        'extension' => $fileData['extension'],
                        'mimeType' => $fileData['mimeType'],
                        'copyright' => $data[$columnWithPictureCopyRightText] ?? '',
                        'description' => $fileData['name'] ?? ''
                    ];
                    $existingImageIds[] = $fileData['id'];
                } elseif (!$isImage && !in_array($fileData['id'], $existingFileIds)) {
                    $payload['files'][] = [
                        'id' => $fileData['id'],
                        'name' => $fileData['name'],
                        'extension' => $fileData['extension'],
                        'mimeType' => $fileData['mimeType'],
                        'description' => $fileData['name'] ?? '',
                    ];
                    $existingFileIds[] = $fileData['id'];
                }
            } catch (\Exception $e) {
                continue;
            }
        }
    }

    /**
     * Generate a preview of the import data
     * 
     * @param ProjectImport $import The import object
     * @return array Array of preview items with validation status
     */
    public function generatePreview(ProjectImport $import): array
    {
        try {
            $filePath = $import->getFilePath();
            if (!file_exists($filePath)) {
                $filePath = $this->uploadDir . '/' . $filePath;
            }
            
            $spreadsheet = IOFactory::load($filePath);
            $worksheet = $spreadsheet->getActiveSheet();
            
            $headerRowIndex = $this->getHeaderRowCount();
            $highestRow = $worksheet->getHighestRow();
            $highestColumnIndex = Coordinate::columnIndexFromString($worksheet->getHighestColumn());
            
            $headerMapping = [];
            $previewData = [];
            
            // Get parent headers from row 1
            $parentHeaders = [];
            for ($col = 1; $col <= $highestColumnIndex; $col++) {
                $parentHeader = $worksheet->getCellByColumnAndRow($col, 1)->getValue();
                if (!empty($parentHeader)) {
                    $parentHeaders[$col] = $parentHeader;
                }
            }
            
            // Build header mapping from row 4 (field names)
            for ($col = 1; $col <= $highestColumnIndex; $col++) {
                $columnLetter = Coordinate::stringFromColumnIndex($col);
                $fieldName = $worksheet->getCellByColumnAndRow($col, 4)->getValue();
                if (!empty($fieldName)) {
                    $currentParent = null;
                    foreach ($parentHeaders as $parentCol => $parentValue) {
                        if ($parentCol <= $col) {
                            $currentParent = $parentValue;
                        }
                    }
                    
                    $headerMapping[$columnLetter] = [
                        'field' => $fieldName,
                        'parent' => $currentParent
                    ];
                }
            }
            
            $leCategoryNameMapping = $this->getLeCategoryNameMapping();
            $localWorkgroupNameMapping = $this->getLocalWorkgroupNameMapping();
            
            $maxPreviewRows = 100;
            $rowLimit = min($highestRow, $maxPreviewRows + $headerRowIndex);
            
            for ($rowIndex = $headerRowIndex + 1; $rowIndex <= $rowLimit; $rowIndex++) {
                $rowData = [];
                
                for ($col = 1; $col <= $highestColumnIndex; $col++) {
                    $columnLetter = Coordinate::stringFromColumnIndex($col);
                    $value = $worksheet->getCellByColumnAndRow($col, $rowIndex)->getValue();
                    
                    if (isset($headerMapping[$columnLetter]) && is_array($headerMapping[$columnLetter])) {
                        $fieldName = $headerMapping[$columnLetter]['field'];
                        $rowData[$fieldName] = $value;
                    }
                    
                    $rowData[$columnLetter] = $value;
                }
                
                if (empty($rowData)) {
                    continue;
                }
                
                $payload = $this->preparePreviewPayload($rowData);
                
                if (isset($payload['leFundingCategoryId']) && is_numeric($payload['leFundingCategoryId'])) {
                    $excelCategoryId = (int)$payload['leFundingCategoryId'];
                    if (isset($leCategoryNameMapping[$excelCategoryId])) {
                        $payload['leFundingCategoryName'] = $leCategoryNameMapping[$excelCategoryId];
                    }
                }
                
                if (isset($payload['localWorkgroupId']) && is_numeric($payload['localWorkgroupId'])) {
                    $excelWorkgroupId = (int)$payload['localWorkgroupId'];
                    if (isset($localWorkgroupNameMapping[$excelWorkgroupId])) {
                        $payload['localWorkgroupName'] = $localWorkgroupNameMapping[$excelWorkgroupId];
                    }
                }
                
                $previewItem = [
                    'rowNumber' => $rowIndex - $headerRowIndex,
                    'title' => $payload['title'] ?? 'Untitled',
                    'description' => $payload['description'] ?? '',
                    'projectCode' => $payload['projectCode'] ?? '',
                    'startDate' => $payload['startDate'] ?? null,
                    'endDate' => $payload['endDate'] ?? null,
                    'status' => 'valid',
                    'message' => '',
                    'payload' => $payload
                ];
                
                if (empty($previewItem['title'])) {
                    $previewItem['status'] = 'error';
                    $previewItem['message'] = 'Project title (Q2.1) is required';
                } elseif ($this->findProjectByTitle($previewItem['title'])) {
                    $previewItem['status'] = 'warning';
                    $previewItem['message'] = 'Projekt mit diesem Namen existiert bereits';
                }
                
                $previewData[] = $previewItem;
            }
            
            return $previewData;
        } catch (\Exception $e) {
            return [
                [
                    'rowNumber' => 1,
                    'title' => 'Error generating preview',
                    'description' => 'An error occurred: ' . $e->getMessage(),
                    'projectCode' => '',
                    'startDate' => null,
                    'endDate' => null,
                    'status' => 'error',
                    'message' => $e->getMessage(),
                    'payload' => []
                ]
            ];
        }
    }

    /**
     * Import projects from the Excel file
     * 
     * @param ProjectImport $import The import object
     * @param User $user The user performing the import
     * @param LEPeriod|null $lePeriod Optional LE period to associate with imported projects
     * @return bool True if import was successful, false otherwise
     */
    public function importProjects(ProjectImport $import, User $user, ?LEPeriod $lePeriod = null): bool
    {
        try {
            $leCategoryMapping = $this->getLeCategoryMapping();
            $localWorkgroupMapping = $this->getLocalWorkgroupMapping();
            
            $import->setStatus(ProjectImport::STATUS_PROCESSING)
                  ->setUpdatedAt(new \DateTime());
            $this->em->persist($import);
            $this->em->flush();
            
            $items = $import->getItems();
            
            if ($items->isEmpty()) {
                $filePath = $import->getFilePath();
                if (!file_exists($filePath)) {
                    $filePath = $this->uploadDir . '/' . $filePath;
                }
                
                $spreadsheet = IOFactory::load($filePath);
                $worksheet = $spreadsheet->getActiveSheet();
                $highestRow = $worksheet->getHighestRow();
                $highestColumnIndex = Coordinate::columnIndexFromString($worksheet->getHighestColumn());
                
                $headerMapping = [];
                $parentHeaders = [];
                
                for ($col = 1; $col <= $highestColumnIndex; $col++) {
                    $parentHeader = $worksheet->getCellByColumnAndRow($col, 1)->getValue();
                    if (!empty($parentHeader)) {
                        $parentHeaders[$col] = $parentHeader;
                    }
                }
                
                for ($col = 1; $col <= $highestColumnIndex; $col++) {
                    $fieldName = $worksheet->getCellByColumnAndRow($col, 4)->getValue();
                    if (!empty($fieldName)) {
                        $currentParent = null;
                        foreach ($parentHeaders as $parentCol => $parentValue) {
                            if ($parentCol <= $col) {
                                $currentParent = $parentValue;
                            }
                        }
                        
                        $headerMapping[$col] = [
                            'parent' => $currentParent,
                            'field' => $fieldName
                        ];
                    }
                }
                
                for ($row = 5; $row <= $highestRow; $row++) {
                    $rowData = [];
                    
                    for ($col = 1; $col <= $highestColumnIndex; $col++) {
                        if (isset($headerMapping[$col])) {
                            $cellValue = $worksheet->getCellByColumnAndRow($col, $row)->getValue();
                            $fieldName = $headerMapping[$col]['field'];
                            $rowData[$fieldName] = $cellValue;
                        }
                        
                        $colLetter = Coordinate::stringFromColumnIndex($col);
                        $cellValue = $worksheet->getCellByColumnAndRow($col, $row)->getValue();
                        if ($cellValue !== null) {
                            $rowData[$colLetter] = $cellValue;
                        }
                    }
                    
                    if (empty($rowData)) {
                        continue;
                    }
                    
                    $payload = $this->prepareProjectPayload($rowData);
                    
                    $importItem = new ProjectImportItem();
                    $importItem->setImport($import)
                              ->setRowNumber($row - 4)
                              ->setStatus(ProjectImportItem::STATUS_PENDING)
                              ->setRawData($rowData)
                              ->setProcessedData([
                                  'rowNumber' => $row - 4,
                                  'title' => $payload['title'] ?? 'Untitled',
                                  'description' => $payload['description'] ?? '',
                                  'projectCode' => $payload['projectCode'] ?? '',
                                  'startDate' => $payload['startDate'] ?? null,
                                  'endDate' => $payload['endDate'] ?? null,
                                  'status' => 'valid',
                                  'message' => '',
                                  'payload' => $payload
                              ])
                              ->setCreatedAt(new \DateTime())
                              ->setUpdatedAt(new \DateTime());
                    
                    $this->em->persist($importItem);
                }
                
                $this->em->flush();
                $this->em->refresh($import);
                $items = $import->getItems();
            }
            
            $successCount = $errorCount = $processedCount = 0;
            
            $import->setProcessedRows($processedCount);
            $this->em->persist($import);
            $this->em->flush();
            
            foreach ($items as $item) {
                if ($item->getStatus() !== ProjectImportItem::STATUS_PENDING) {
                    continue;
                }
                
                $item->setStatus(ProjectImportItem::STATUS_PROCESSING)
                     ->setUpdatedAt(new \DateTime());
                $this->em->persist($item);
                $this->em->flush();
                
                $processedData = $item->getProcessedData();
                if (!$processedData || !isset($processedData['payload'])) {
                    $item->setStatus(ProjectImportItem::STATUS_FAILED)
                         ->setErrorMessage('No processed data available')
                         ->setUpdatedAt(new \DateTime());
                    $this->em->persist($item);
                    
                    $errorCount++;
                    $processedCount++;
                    
                    $this->updateImportProgress($import, $processedCount, $successCount, $errorCount);
                    continue;
                }
                
                $result = $processedData;
                if ($result['status'] === 'error') {
                    $item->setStatus(ProjectImportItem::STATUS_FAILED)
                         ->setErrorMessage($result['message'])
                         ->setUpdatedAt(new \DateTime());
                    $this->em->persist($item);
                    
                    $errorCount++;
                    $processedCount++;
                    
                    $this->updateImportProgress($import, $processedCount, $successCount, $errorCount);
                    continue;
                }
                
                try {
                    if ($lePeriod) {
                        $result['payload']['lePeriod'] = $lePeriod;
                        
                        if (isset($result['payload']['leFundingCategoryId']) && is_numeric($result['payload']['leFundingCategoryId'])) {
                            $excelCategoryId = (int)$result['payload']['leFundingCategoryId'];
                            $dbCategoryId = $leCategoryMapping[$excelCategoryId] ?? null;
                            
                            if ($dbCategoryId) {
                                $leCategory = $this->em->getRepository(LEFundingCategory::class)->find($dbCategoryId);
                                if ($leCategory) {
                                    $result['payload']['leFundingCategory'] = $leCategory;
                                }
                            }
                        }
                    }
                    
                    if (isset($result['payload']['localWorkgroupId']) && is_numeric($result['payload']['localWorkgroupId'])) {
                        $excelWorkgroupId = (int)$result['payload']['localWorkgroupId'];
                        $dbWorkgroupId = $localWorkgroupMapping[$excelWorkgroupId] ?? null;
                        
                        if ($dbWorkgroupId) {
                            $localWorkgroup = $this->em->getRepository(LocalWorkgroup::class)->find($dbWorkgroupId);
                            if ($localWorkgroup) {
                                $result['payload']['localWorkgroup'] = $localWorkgroup;
                            }
                        }
                    }
                    
                    $existingProject = $this->findProjectByTitle($result['payload']['title']);
                    
                    if ($existingProject) {
                        $this->projectService->updateProject($existingProject, $result['payload']);
                        
                        $item->setStatus(ProjectImportItem::STATUS_COMPLETED)
                             ->setProject($existingProject)
                             ->setUpdatedAt(new \DateTime());
                        $this->em->persist($item);
                        $this->em->flush();
                        
                        $successCount++;
                        $processedCount++;
                        
                        $this->updateImportProgress($import, $processedCount, $successCount, $errorCount);
                    } else {
                        $result['payload']['user'] = $user;
                        $project = $this->projectService->createProject($result['payload']);
                        
                        $item->setStatus(ProjectImportItem::STATUS_COMPLETED)
                             ->setProject($project)
                             ->setUpdatedAt(new \DateTime());
                        $this->em->persist($item);
                        $this->em->flush();
                        
                        $successCount++;
                        $processedCount++;
                        
                        $this->updateImportProgress($import, $processedCount, $successCount, $errorCount);
                    }
                } catch (\Exception $e) {
                    $item->setStatus(ProjectImportItem::STATUS_FAILED)
                         ->setErrorMessage($e->getMessage())
                         ->setUpdatedAt(new \DateTime());
                    $this->em->persist($item);
                    
                    $errorCount++;
                    $processedCount++;
                    
                    $this->updateImportProgress($import, $processedCount, $successCount, $errorCount);
                }
            }
            
            $import->setStatus(ProjectImport::STATUS_COMPLETED)
                  ->setProcessedRows($processedCount)
                  ->setSuccessfulRows($successCount)
                  ->setErrorRows($errorCount)
                  ->setUpdatedAt(new \DateTime());
            $this->em->persist($import);
            $this->em->flush();
            
            return true;
        } catch (\Exception $e) {
            $import->setStatus(ProjectImport::STATUS_FAILED)
                  ->setErrorMessage($e->getMessage())
                  ->setUpdatedAt(new \DateTime());
            $this->em->persist($import);
            $this->em->flush();
            
            return false;
        }
    }

    /**
     * Update import progress
     */
    private function updateImportProgress(ProjectImport $import, int $processedCount, int $successCount, int $errorCount): void
    {
        $import->setProcessedRows($processedCount)
               ->setSuccessfulRows($successCount)
               ->setErrorRows($errorCount)
               ->setUpdatedAt(new \DateTime());
        $this->em->persist($import);
        $this->em->flush();
    }

    /**
     * Get the mapping between Excel LE-Category IDs and their names
     * 
     * @return array Mapping from Excel ID to category name
     */
    protected function getLeCategoryNameMapping(): array
    {
        return [
            1 => '73-01 Investitionen in die landwirtschaftliche Erzeugung',
            2 => '73-08 Investitionen in Diversifizierungsaktivitäten inklusive Be- und Verarbeitung sowie Vermarktung landwirtschaftlicher Erzeugnisse',
            3 => '73-10 Orts- und Stadtkernförderung (Investitionen zur Revitalisierung und Sanierung oder Um- und Weiterbau von leerstehenden, fehl- oder mindergenutzten Gebäuden oder öffentlichen Flächen)',
            4 => '73-11 Soziale Dienstleistungen',
            5 => '73-15 Investitionen zur Erhaltung, Wiederherstellung und Verbesserung des natürlichen Erbes',
            6 => '75-02 Unterstützung der Gründung und Entwicklung von innovativen Kleinunternehmen mit Mehrwert für den ländlichen Raum',
            7 => '77-02 Soziale Landwirtschaft',
            8 => '77-02 Lokale Märkte/Absatzförderung',
            9 => '77-02 Erzeugerorganisationen',
            10 => '77-02 LMQ',
            11 => '77-02 Cluster',
            12 => '77-02 Tourismusdienstleistungen',
            13 => '77-02 Arbeitsabläufe, Ressourcennutzung',
            14 => '77-02 Bioökonomie',
            15 => '77-02 Kulinarik',
            16 => '77-02 Digitalisierung und sonstiges',
            17 => '77-02 Forstwirtschaft',
            18 => '77-02 Tourismus Pilotprojekte',
            19 => '77-02 Naturschutz',
            20 => '77-02 Naturschutz BL',
            21 => '77-02 Naturschutz BMK',
            22 => '77-02- Nationalparke',
            23 => '77-02 Umweltschutz BML',
            24 => '77-02 Alpenkonvention',
            25 => '77-03 Ländliche Innovationssysteme',
            26 => '77-04 Reaktivierung des Leerstands durch Bewusstseinsbildung & Beratung, Entwicklungskonzepte & Management zur Stadt- und Ortskernstärkung',
            27 => '77-05 LEADER',
            28 => '77-06 Förderung von Operationellen Gruppen und von Innovationsprojekten im Rahmen der Europäischen Innovationspartnerschaft für landwirtschaftliche Produktivität und Nachhaltigkeit – EIP-AGRI',
            29 => '78-02 Wissenstransfer für land- und forstwirtschaftliche Themenfelder(fachliche und persönliche Fort- und Weiterbildung und Information)',
            30 => '78-03 Pädagogik LW, Umw., Ernähr.',
            31 => '78-03 Dialog mit der Gesellschaft LW, Umw., Ernähr.',
            32 => '78-03 Waldbezogene Pläne, Natur- und Gesellschaftsthemen',
            33 => '78-03-4WT Weiterbildung Mgmt in Regionen',
            34 => '78-03 Naturschutz BL',
            35 => '78-03 Naturschutz BMK',
            36 => '78-03 Nationalparke',
            37 => '78-03 Alpenkonvention'
        ];
    }

    /**
     * Get the mapping between Excel LE-Category IDs and database LE-Category IDs
     * 
     * @return array Mapping from Excel ID to database ID
     */
    private function getLeCategoryMapping(): array
    {
        return [
            1 => 26,  // 73-01 Investitionen in die landwirtschaftliche Erzeugung
            2 => 27,  // 73-08 Investitionen in Diversifizierungsaktivitäten...
            3 => 28,  // 73-10 Orts- und Stadtkernförderung...
            4 => 29,  // 73-11 Soziale Dienstleistungen
            5 => 30,  // 73-15 Investitionen zur Erhaltung...
            6 => 31,  // 75-02 Unterstützung der Gründung...
            7 => 32,  // 77-02 Soziale Landwirtschaft
            8 => 33,  // 77-02 Lokale Märkte/Absatzförderung
            9 => 34,  // 77-02 Erzeugerorganisationen
            10 => 35, // 77-02 LMQ
            11 => 36, // 77-02 Cluster
            12 => 37, // 77-02 Tourismusdienstleistungen
            13 => 38, // 77-02 Arbeitsabläufe, Ressourcennutzung
            14 => 39, // 77-02 Bioökonomie
            15 => 40, // 77-02 Kulinarik
            16 => 41, // 77-02 Digitalisierung und sonstiges
            17 => 42, // 77-02 Forstwirtschaft
            18 => 43, // 77-02 Tourismus Pilotprojekte
            19 => 44, // 77-02 Naturschutz
            20 => 45, // 77-02 Naturschutz BL
            21 => 46, // 77-02 Naturschutz BMK
            22 => 47, // 77-02- Nationalparke
            23 => 48, // 77-02 Umweltschutz BML
            24 => 49, // 77-02 Alpenkonvention
            25 => 50, // 77-03 Ländliche Innovationssysteme
            26 => 51, // 77-04 Reaktivierung des Leerstands...
            27 => 52, // 77-05 LEADER
            28 => 53, // 77-06 Förderung von Operationellen Gruppen...
            29 => 54, // 78-02 Wissenstransfer für land- und forstwirtschaftliche Themenfelder...
            30 => 55, // 78-03 Pädagogik LW, Umw., Ernähr.
            31 => 56, // 78-03 Dialog mit der Gesellschaft LW, Umw., Ernähr.
            32 => 57, // 78-03 Waldbezogene Pläne, Natur- und Gesellschaftsthemen
            33 => 58, // 78-03-4WT Weiterbildung Mgmt in Regionen
            34 => 59, // 78-03 Naturschutz BL
            35 => 60, // 78-03 Naturschutz BMK
            36 => 61, // 78-03 Nationalparke
            37 => 62  // 78-03 Alpenkonvention
        ];
    }

    /**
     * Get the mapping between Excel LocalWorkgroup IDs and database LocalWorkgroup IDs
     * 
     * @return array Mapping from Excel ID to database ID
     */
    private function getLocalWorkgroupMapping(): array
    {
        return [
            1 => 1,   // BGL01 Nordburgenland plus -> LAG nordburgenland plus
            2 => 78,  // BGL02 Verein Mittelburgenland -> LAG Verein Mittelburgenland
            3 => 3,   // BGL03 Südburgenland plus -> LAG südburgenland plus
            4 => 4,   // KTN01 Regionalmanagement Mittelkärnten -> LAG kärnten:mitte
            5 => 5,   // KTN02 Region Hermagor -> LAG Region Hermagor
            6 => 6,   // KTN03 Regionalkooperation LAG Villach-Umland -> LAG Villach-Umland
            7 => 7,   // KTN04 LAG Großglockner/Mölltal - Oberdrautal -> LAG Großglockner Mölltal - Oberdrautal
            8 => 8,   // KTN05 LAG Nockregion Oberkärnten -> LAG Nockregion Oberkärnten
            9 => 9,   // KTN06 LAG Regionalkooperation Unterkärnten -> LAG Regionalkooperation Unterkärnten
            10 => 79, // KTN07 LAG Regionalkooperation Carnica-Klagenfurt-Umland -> No match
            11 => 10,  // NOE01 LEADER Region Tourismusverband Moststraße -> LAG Tourismusverband Moststraße
            12 => 11,  // NOE02 LEADER-Region NÖ Süd -> LAG Niederösterreich Süd
            13 => 12,  // NOE03 LEADER-Region Mostviertel-Mitte -> LAG Mostviertel Mitte
            14 => 13,  // NOE04 LAG Donau NÖ-Mitte -> LAG Donau Niederösterreich-Mitte
            15 => 80, // NOE05 Bucklige Welt -Wechselland -> No match
            16 => 15,  // NOE06 LEADER Region Triestingtal -> LAG Triestingtal
            17 => 16,  // NOE07 Südliches Waldviertel - Nibelungengau -> LAG Südliches Waldviertel - Nibelungengau
            18 => 17,  // NOE08 Verein Waldviertel Grenzland -> LAG Waldviertler Grenzland
            19 => 18,  // NOE09 Waldviertel Wohlviertel Thayatal -> LAG Waldviertler Wohlviertel Region Nationalpark Thayaland
            20 => 19,  // NOE10 Römerland Carnuntum -> LAG Römerland Carnuntum
            21 => 20,  // NOE11 LEADER Verein Wachau - Dunkelsteinerwald -> LAG Wachau - Dunkelsteinerwald
            22 => 21,  // NOE12 Weinviertel Ost -> LAG Weinviertel Ost
            23 => 22,  // NOE13 LAG Weinviertel Manhartsberg -> LAG Weinviertel Manhartsberg
            24 => 23,  // NOE14 Weinviertel Donauraum -> LAG Weinviertel - Donauraum
            25 => 24,  // NOE15 Kamptal -> LAG Kamptal
            26 => 25,  // NOE16 Elsbeere Wienerwald -> LAG Elsbeere Wienerwald
            27 => 26,  // NOE17 LEADER Region Marchfeld -> LAG Marchfeld
            28 => 27,  // NOE18 Region Eisenstraße Niederösterreich -> LAG Eisenstraße Niederösterreich
            29 => 81, // NOE19 LEADER Thayaland -> No match
            30 => 28,  // OOE01 LAG Perg-Strudengau -> LAG Perg-Strudengau
            31 => 29,  // OOE02 Verband Mühlviertler Alm -> LAG Mühlviertler Alm
            32 => 30,  // OOE03 Verin Zukunft Oberinnviertel-Mattigtal -> LAG Oberinnviertel-Mattigtal
            33 => 31,  // OOE04 Verein REGIS Regionalentwicklung Inneres Salzkammergut -> LAG Kulturerbe Salzkammergut REGIS
            34 => 32,  // OOE05 LEADER-Regionalverein DONAU-BÖHMERWALD -> LAG Donau-Böhmerwald
            35 => 33,  // OOE06 Verein Regionalentwicklung Vöckla-Ager -> LAG Regionalentwicklung Vöckla-Ager
            36 => 34,  // OOE07 Regionsverband Sauwald - Pramtal -> LAG Sauwald - Pramtal
            37 => 35,  // OOE08 Attersee - Attergau -> LAG Attersee - Attergau (REGATTA)
            38 => 36,  // OOE09 Traunsteinregion -> LAG Traunsteinregion
            39 => 37,  // OOE10 LAG Sterngartl-Gusental -> LAG Sterngartl Gusental
            40 => 38,  // OOE11 Region Wels Land -> LAG Wels-LEWEL
            41 => 39,  // OOE12 Region Urfahr West -> LAG Region u.we (Urfahr West)
            42 => 40,  // OOE13 LAG Mostlandl Hausruck -> LAG Mostlandl Hausruck
            43 => 41,  // OOE14 LEADER Mitten im Innviertel -> LAG LEADER Mitten im Innviertel
            44 => 42,  // OOE15 LEADER Region Nationalpark Kalkalpen -> LAG Nationalpark OÖ Kalkalpen
            45 => 43,  // OOE16 Mühlviertler Kernland -> LAG Mühlviertler Kernland
            46 => 44,  // OOE17 Traunviertler Alpenvorland -> LAG Traunviertler Alpenvorland
            47 => 45,  // OOE18 Obst- und Gemüseregion Eferding -> LAG Eferdinger Land
            48 => 46,  // OOE19 Regionalentwicklungsverein Zukunft Linz-Land -> LAG Zukunft Linz-Land
            49 => 47,  // SBG01 Nationalpark Hohe Tauern -> LAG Nationalpark Hohe Tauern
            50 => 48,  // SBG02 Lokale Aktionsgruppe Salzburger Seenland -> LAG Salzburger Seenland
            51 => 49,  // SBG03 Lebenswert Pongau -> LAG Lebens.Wert.Pongau
            52 => 50,  // SBG04 LEADER Verein Saalach-tal -> LAG Saalachtal
            53 => 51,  // SBG05 Biosphärenpark Lungau -> LAG Biosphäre Lungau
            54 => 52,  // SBG06 LEADER Region FUMO -> LAG FUMO Regionalentwicklung Fuschlseeregion - Mondseeland
            55 => 82, // SBG07 LEADER Flachgau Nord -> No match
            56 => 53,  // STM01 LAG Ennstal-Ausseerland -> LAG Ennstal-Ausseerland
            57 => 54,  // STM02 InnovationsRegion Murtal -> LAG InnovationsRegion Murtal
            58 => 55,  // STM03 Mariazellerland Mürztal -> LAG Mariazellerland Mürztal
            59 => 56,  // STM04 Hügelland östlich von Graz - Schöcklland -> LAG Hügelland östlich von Graz - Schöcklland
            60 => 57,  // STM05 Joglland -> LAG Kraftspendedörfer Joglland
            61 => 58,  // STM06 Almenland & Energieregion Weiz - Gleisdorf -> LAG Almenland & Energieregion Weiz - Gleisdorf
            62 => 59,  // STM07 LAG Liezen Gesäuse -> LAG Liezen Gesäuse
            63 => 60,  // STM08 LAG Thermenland - Wechselland -> LAG Thermenland - Wechselland
            64 => 61,  // STM09 Südsteiermark LAG -> LAG Südsteiermark
            65 => 62,  // STM10 LAG Schilcherland -> LAG Schilcherland
            66 => 63,  // STM11 LAG Steirische Eisenstraße -> LAG Steirische Eisenstraße
            67 => 64,  // STM12 Holzwelt Murau -> LAG Holzwelt Murau
            68 => 65,  // STM13 LEADER Aktionsgruppe Lipizzanerheimat -> LAG Lipizzanerheimat
            69 => 66,  // STM14 Steir. Vulkanland -> LAG Steirisches Vulkanland
            70 => 67,  // STM15 LAG Oststeirisches Kernland -> LAG Oststeirisches Kernland
            71 => 83, // STM16 LAG Graz Umgebung Nord -> No match
            72 => 68,  // TIR01 Verein Regionalmangement Bezirk Imst -> LAG Regionalmangement Bezirk Imst
            73 => 69,  // TIR02 Verein Regionalentwicklung Außerfern -REA -> LAG Regionalentwicklung Außerfern - REA
            74 => 70,  // TIR03 RegioL Regionalmanagement Landeck -> LAG Regionalmanagement Landeck - RegioL
            75 => 71,  // TIR04 Regio Tech 1, Regionalentwicklung pillerseetal/leogang/ kitzbühler alpen -> LAG Regionalmanagement regio³ Pillerseetal-Leukental-Leogang
            76 => 72,  // TIR05 Region Kitzbühler Alpen -> LAG Kitzbühler Alpen
            77 => 73,  // TIR06 Regionsmanagement Osttirol -> LAG Regionsmanagement Osttirol
            78 => 74,  // TIR07 Regionalmanagement Wipptal -> LAG Regionalmanagement Wipptal
            79 => 75,  // TIR08 LEADER Region Kufstein und Umgebung - Untere Schranne - Kaiserwinkel -> LAG Kufstein und Umgebung – Untere Schranne – Kaiserwinkel
            80 => 84, // TIR09 Regionalmanagement Bezirk Schwaz -> No match
            81 => 85, // TIR10 Regionalmanagement Innnsbruck Land -> No match
            82 => 76,  // VBG01 Regionalentwicklung Vorarlberg -> LAG REGIO-V Regionalentwicklung Vorarlberg
            83 => 77   // VBG02 LEADER-Region Vorder-land Walgau - Bludenz -> LAG Vorderland - Walgau - Bludenz
        ];
    }

    /**
     * Get a mapping of LocalWorkgroup IDs to their names
     * 
     * @return array Associative array mapping numeric IDs to string names
     */
    protected function getLocalWorkgroupNameMapping(): array
    {
        return [
            1 => 'BGL01 Nordburgenland plus',
            2 => 'BGL02 Verein Mittelburgenland',
            3 => 'BGL03 Südburgenland plus',
            4 => 'KTN01 Regionalmanagement Mittelkärnten',
            5 => 'KTN02 Region Hermagor',
            6 => 'KTN03 Regionalkooperation LAG Villach-Umland',
            7 => 'KTN04 LAG Großglockner/Mölltal - Oberdrautal',
            8 => 'KTN05 LAG Nockregion Oberkärnten',
            9 => 'KTN06 LAG Regionalkooperation Unterkärnten',
            10 => 'KTN07 LAG Regionalkooperation Carnica-Klagenfurt-Umland',
            11 => 'NOE01 LEADER Region Tourismusverband Moststraße',
            12 => 'NOE02 LEADER-Region NÖ Süd',
            13 => 'NOE03 LEADER-Region Mostviertel-Mitte',
            14 => 'NOE04 LAG Donau NÖ-Mitte',
            15 => 'NOE05 Bucklige Welt -Wechselland',
            16 => 'NOE06 LEADER Region Triestingtal',
            17 => 'NOE07 Südliches Waldviertel - Nibelungengau',
            18 => 'NOE08 Verein Waldviertel Grenzland',
            19 => 'NOE09 Waldviertel Wohlviertel Thayatal',
            20 => 'NOE10 Römerland Carnuntum',
            21 => 'NOE11 LEADER Verein Wachau - Dunkelsteinerwald',
            22 => 'NOE12 Weinviertel Ost',
            23 => 'NOE13 LAG Weinviertel Manhartsberg',
            24 => 'NOE14 Weinviertel Donauraum',
            25 => 'NOE15 Kamptal',
            26 => 'NOE16 Elsbeere Wienerwald',
            27 => 'NOE17 LEADER Region Marchfeld',
            28 => 'NOE18 Region Eisenstraße Niederösterreich',
            29 => 'NOE19 LEADER Thayaland',
            30 => 'OOE01 LAG Perg-Strudengau',
            31 => 'OOE02 Verband Mühlviertler Alm',
            32 => 'OOE03 Verin Zukunft Oberinnviertel-Mattigtal',
            33 => 'OOE04 Verein REGIS Regionalentwicklung Inneres Salzkammergut',
            34 => 'OOE05 LEADER-Regionalverein DONAU-BÖHMERWALD',
            35 => 'OOE06 Verein Regionalentwicklung Vöckla-Ager',
            36 => 'OOE07 Regionsverband Sauwald - Pramtal',
            37 => 'OOE08 Attersee - Attergau',
            38 => 'OOE09 Traunsteinregion',
            39 => 'OOE10 LAG Sterngartl-Gusental',
            40 => 'OOE11 Region Wels Land',
            41 => 'OOE12 Region Urfahr West',
            42 => 'OOE13 LAG Mostlandl Hausruck',
            43 => 'OOE14 LEADER Mitten im Innviertel',
            44 => 'OOE15 LEADER Region Nationalpark Kalkalpen',
            45 => 'OOE16 Mühlviertler Kernland',
            46 => 'OOE17 Traunviertler Alpenvorland',
            47 => 'OOE18 Obst- und Gemüseregion Eferding',
            48 => 'OOE19 Regionalentwicklungsverein Zukunft Linz-Land',
            49 => 'SBG01 Nationalpark Hohe Tauern',
            50 => 'SBG02 Lokale Aktionsgruppe Salzburger Seenland',
            51 => 'SBG03 Lebenswert Pongau',
            52 => 'SBG04 LEADER Verein Saalach-tal',
            53 => 'SBG05 Biosphärenpark Lungau',
            54 => 'SBG06 LEADER Region FUMO',
            55 => 'SBG07 LEADER Flachgau Nord',
            56 => 'STM01 LAG Ennstal-Ausseerland',
            57 => 'STM02 InnovationsRegion Murtal',
            58 => 'STM03 Mariazellerland Mürztal',
            59 => 'STM04 Hügelland östlich von Graz - Schöcklland',
            60 => 'STM05 Joglland',
            61 => 'STM06 Almenland & Energieregion Weiz - Gleisdorf',
            62 => 'STM07 LAG Liezen Gesäuse',
            63 => 'STM08 LAG Thermenland - Wechselland',
            64 => 'STM09 Südsteiermark LAG',
            65 => 'STM10 LAG Schilcherland',
            66 => 'STM11 LAG Steirische Eisenstraße',
            67 => 'STM12 Holzwelt Murau',
            68 => 'STM13 LEADER Aktionsgruppe Lipizzanerheimat',
            69 => 'STM14 Steir. Vulkanland',
            70 => 'STM15 LAG Oststeirisches Kernland',
            71 => 'STM16 LAG Graz Umgebung Nord',
            72 => 'TIR01 Verein Regionalmangement Bezirk Imst',
            73 => 'TIR02 Verein Regionalentwicklung Außerfern -REA',
            74 => 'TIR03 RegioL Regionalmanagement Landeck',
            75 => 'TIR04 Regio Tech 1, Regionalentwicklung pillerseetal/leogang/ kitzbühler alpen',
            76 => 'TIR05 Region Kitzbühler Alpen',
            77 => 'TIR06 Regionsmanagement Osttirol',
            78 => 'TIR07 Regionalmanagement Wipptal',
            79 => 'TIR08 LEADER Region Kufstein und Umgebung - Untere Schranne - Kaiserwinkel',
            80 => 'TIR09 Regionalmanagement Bezirk Schwaz',
            81 => 'TIR10 Regionalmanagement Innnsbruck Land',
            82 => 'VBG01 Regionalentwicklung Vorarlberg',
            83 => 'VBG02 LEADER-Region Vorder-land Walgau - Bludenz'
        ];
    }

    /**
     * Downloads a file from a URL and saves it as a File entity
     * 
     * @param string $url The URL to download from
     * @param string $filename The filename to save as
     * @return array|null The file data or null if download failed
     */
    private function downloadFileFromUrl(string $url, string $filename): ?array
    {
        try {
            // Ensure URL has a protocol
            if (!preg_match('~^(?:f|ht)tps?://~i', $url)) {
                $url = 'https://' . $url;
            }
            
            // URL encode any spaces or special characters in the URL path
            // But preserve the basic URL structure
            $urlParts = parse_url($url);
            if (isset($urlParts['path'])) {
                // Only encode the path portion
                $encodedPath = implode('/', array_map('rawurlencode', explode('/', $urlParts['path'])));
                
                // Reconstruct the URL
                $scheme = isset($urlParts['scheme']) ? $urlParts['scheme'] . '://' : 'https://';
                $host = $urlParts['host'] ?? '';
                $port = isset($urlParts['port']) ? ':' . $urlParts['port'] : '';
                $query = isset($urlParts['query']) ? '?' . $urlParts['query'] : '';
                $fragment = isset($urlParts['fragment']) ? '#' . $urlParts['fragment'] : '';
                
                $url = $scheme . $host . $port . $encodedPath . $query . $fragment;
            }
            
            // Clean up the filename - remove any potentially problematic characters
            $cleanFilename = $this->sanitizeFilename($filename);
            
            
            // Set up context with timeout and user agent
            $context = stream_context_create([
                'http' => [
                    'timeout' => 30, // 30 seconds timeout
                    'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36',
                    'follow_location' => 1,
                    'max_redirects' => 5
                ],
                'ssl' => [
                    'verify_peer' => false,
                    'verify_peer_name' => false
                ]
            ]);
            
            // Download the file
            $fileContents = null;
            $attempts = 0;
            $maxAttempts = 3;
            
            while ($attempts < $maxAttempts) {
                $attempts++;
                
                try {
                    $fileContents = @file_get_contents($url, false, $context);
                    if ($fileContents !== false) {
                        break; // Success, exit the loop
                    }
                    
                    $error = error_get_last();
                    
                    // Wait before retrying
                    if ($attempts < $maxAttempts) {
                        sleep(1);
                    }
                } catch (\Exception $e) {
                    
                    // Wait before retrying
                    if ($attempts < $maxAttempts) {
                        sleep(1);
                    }
                }
            }
            
            if ($fileContents === false || $fileContents === null) {
                $error = error_get_last();

                return null;
            }
            
            // Check if we got an empty response
            if (empty($fileContents)) {

                return null;
            }
            
            // Determine if it's an image or document
            $isImage = $this->isImageFile($filename);
            $extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
            $mimeType = $this->getMimeTypeFromFilename($filename);
            
            // Convert to base64 data
            $base64Data = 'data:' . $mimeType . ';base64,' . base64_encode($fileContents);
            
            // Create a File entity
            $file = new \App\Entity\File();
            $file
                ->setName($cleanFilename)
                ->setCreatedAt(new \DateTime())
                ->setData($base64Data)
                ->setHash(md5($base64Data))
                ->setMimeType($mimeType)
                ->setExtension($extension);
            
            // Check if a file with the same hash already exists
            $existingFile = $this->em->getRepository(\App\Entity\File::class)->findOneBy([
                'hash' => $file->getHash(),
            ]);
            
            if (!$existingFile) {
                $this->em->persist($file);
                $this->em->flush();
            } else {
                $file = $existingFile;
            }
            
            // Return the file data in the format expected by the Project entity
            return [
                'id' => $file->getId(),
                'name' => $cleanFilename,
                'originalName' => $filename,
                'extension' => $extension,
                'mimeType' => $mimeType,
                'copyright' => '',
                'description' => ''
            ];
        } catch (\Exception $e) {

            return null;
        }
    }
    
    /**
     * Sanitizes a filename by removing or replacing problematic characters
     * 
     * @param string $filename The filename to sanitize
     * @return string The sanitized filename
     */
    private function sanitizeFilename(string $filename): string
    {
        // Get the file extension
        $pathInfo = pathinfo($filename);
        $extension = isset($pathInfo['extension']) ? strtolower($pathInfo['extension']) : '';
        $basename = $pathInfo['filename'] ?? '';
        
        // Handle special case where extension might be uppercase in the original filename
        if (empty($extension) && strpos($filename, '.') !== false) {
            $parts = explode('.', $filename);
            $extension = strtolower(end($parts));
            array_pop($parts);
            $basename = implode('.', $parts);
        }
        
        // Transliterate non-ASCII characters to their ASCII equivalents
        $basename = $this->transliterateString($basename);
        
        // Replace spaces and special characters with underscores
        $basename = preg_replace('/[^a-zA-Z0-9_.-]/', '_', $basename);
        
        // Remove multiple consecutive underscores
        $basename = preg_replace('/_+/', '_', $basename);
        
        // Trim underscores from beginning and end
        $basename = trim($basename, '_');
        
        // Ensure the filename is not empty
        if (empty($basename)) {
            $basename = 'file_' . uniqid();
        }
        
        // Reconstruct the filename with the original extension
        return $basename . ($extension ? '.' . $extension : '');
    }
    
    /**
     * Transliterates a string, converting non-ASCII characters to their ASCII equivalents
     * 
     * @param string $string The string to transliterate
     * @return string The transliterated string
     */
    private function transliterateString(string $string): string
    {
        // Define character mappings for common non-ASCII characters
        $characterMap = [
            // German
            'ä' => 'ae', 'ö' => 'oe', 'ü' => 'ue', 'ß' => 'ss',
            'Ä' => 'Ae', 'Ö' => 'Oe', 'Ü' => 'Ue',
            // French
            'é' => 'e', 'è' => 'e', 'ê' => 'e', 'ë' => 'e',
            'à' => 'a', 'â' => 'a', 'ç' => 'c',
            'î' => 'i', 'ï' => 'i',
            'ô' => 'o', 'œ' => 'oe',
            'ù' => 'u', 'û' => 'u', 'ÿ' => 'y',
            'É' => 'E', 'È' => 'E', 'Ê' => 'E', 'Ë' => 'E',
            'À' => 'A', 'Â' => 'A', 'Ç' => 'C',
            'Î' => 'I', 'Ï' => 'I',
            'Ô' => 'O', 'Œ' => 'Oe',
            'Ù' => 'U', 'Û' => 'U', 'Ÿ' => 'Y',
            // Spanish
            'ñ' => 'n', 'Ñ' => 'N',
            'á' => 'a', 'é' => 'e', 'í' => 'i', 'ó' => 'o', 'ú' => 'u',
            'Á' => 'A', 'É' => 'E', 'Í' => 'I', 'Ó' => 'O', 'Ú' => 'U',
            // Other common characters
            '©' => 'c', '®' => 'r', '™' => 'tm',
            '€' => 'euro', '£' => 'pound', '¥' => 'yen',
            '&' => 'and'
        ];
        
        // Apply the character map
        $string = str_replace(array_keys($characterMap), array_values($characterMap), $string);
        
        // If iconv is available, use it for additional transliteration
        if (function_exists('iconv')) {
            $string = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $string);
        }
        
        return $string;
    }

    /**
     * Prepare a lightweight project payload for preview generation
     * 
     * This is a stripped-down version of prepareProjectPayload that skips
     * expensive operations like file downloads and database lookups.
     *
     * @param array $data The Excel row data
     * @return array The payload for preview
     */
    private function preparePreviewPayload(array $data): array
    {
        $payload = [
            'isPublic' => false,
            'projectCode' => $data['Q2.2'] ?? '',
            'title' => $data['Q2.1'] ?? '',
            'keywords' => $data['Q4'] ?? '',
            'description' => $data['Q11'] ?? '',
            'projectCosts' => $data['Q9'] ?? null,
            'cooperationProjectAt' => isset($data['Q8.1']) ? (bool)$data['Q8.1'] : false,
            'cooperationProjectEu' => isset($data['Q8.2']) ? (bool)$data['Q8.2'] : false,
            'topics' => [],
            'tags' => [],
            'geographicRegions' => [],
            'countries' => [],
            'states' => [],
            'programs' => [],
            'instruments' => [],
            'businessSectors' => [],
            'financing' => [],
            'contacts' => [],
            'links' => [],
            'videos' => [],
            'images' => [],
            'files' => [],
            'dates' => [],
            'translations' => [],
            'fundingMethod' => '',
            'lat' => null,
            'lng' => null,
            'localWorkgroup' => null,
            'caseStudy' => false,
            'exemplary' => '',
            'initialContext' => '',
            'initialContextGoals' => '',
            'additionalValue' => '',
            'additionalValueResult' => '',
            'innovations' => '',
            'integrationYoungCitizen' => '',
            'integrationFemaleCitizen' => '',
            'integrationMinorities' => '',
            'learningExperience' => '',
            'transferable' => '',
            'transferDetails' => '',
            'fundingMethodStakeholders' => '',
            'resultsQuality' => '',
            'resultsQuantity' => '',
            // Store the LE-Category ID from column AF
            'leFundingCategoryId' => isset($data['AF']) && is_numeric($data['AF']) ? (int)$data['AF'] : null,
            // Store the LocalWorkgroup ID from column AG
            'localWorkgroupId' => isset($data['AG']) && is_numeric($data['AG']) ? (int)$data['AG'] : null
        ];

        // Parse dates
        if (!empty($data['Q2.3']) || !empty($data['C'])) {
            $startDateValue = $data['Q2.3'] ?? $data['C'] ?? null;
            if ($startDateValue) {
                if ($startDateValue instanceof \DateTime) {
                    $payload['startDate'] = $startDateValue->format('Y-m-d');
                } else if (is_numeric($startDateValue)) {
                    // Excel date
                    $payload['startDate'] = \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($startDateValue)->format('Y-m-d');
                } else {
                    // Try to parse the date string
                    try {
                        $payload['startDate'] = (new \DateTime($startDateValue))->format('Y-m-d');
                    } catch (\Exception $e) {
                        // Ignore date parsing errors for preview
                    }
                }
            }
        }
        
        if (!empty($data['Q2.4']) || !empty($data['D'])) {
            $endDateValue = $data['Q2.4'] ?? $data['D'] ?? null;
            if ($endDateValue) {
                if ($endDateValue instanceof \DateTime) {
                    $payload['endDate'] = $endDateValue->format('Y-m-d');
                } else if (is_numeric($endDateValue)) {
                    // Excel date
                    $payload['endDate'] = \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($endDateValue)->format('Y-m-d');
                } else {
                    // Try to parse the date string
                    try {
                        $payload['endDate'] = (new \DateTime($endDateValue))->format('Y-m-d');
                    } catch (\Exception $e) {
                        // Ignore date parsing errors for preview
                    }
                }
            }
        }

        // Process keywords and convert them to tags (simple version without DB lookups)
        if (!empty($payload['keywords'])) {
            $keywords = explode(',', $payload['keywords']);
            foreach ($keywords as $keyword) {
                $keyword = trim($keyword);
                if (!empty($keyword)) {
                    $payload['tags'][] = [
                        'name' => $keyword,
                        'context' => 'tag'
                    ];
                }
            }
        }

        // Process topics (Q3.1 to Q3.16) - simplified for preview
        $topicMapping = [
            'Q3.1' => 'Klimaschutz',
            'Q3.2' => 'Klimawandelanpassung',
            'Q3.3' => 'Nachhaltige Land- und Forstwirtschaft',
            'Q3.4' => 'Lebensmittelverarbeitung und Kulinarik',
            'Q3.5' => 'Vermarktung und Vertrieb',
            'Q3.6' => 'Umwelt und Biodiversität',
            'Q3.7' => 'Naturschutz',
            'Q3.8' => 'Ländliche Wirtschaft / KMU',
            'Q3.9' => 'Tourismus',
            'Q3.10' => 'Mobilität',
            'Q3.11' => 'Gemeinwohl, Soziales und Daseinsvorsorge',
            'Q3.12' => 'Jugend',
            'Q3.13' => 'Kultur und kulturelles Erbe',
            'Q3.14' => 'Gleichstellung',
            'Q3.15' => 'Digitalisierung',
            'Q3.16' => 'Bildung, Sensibilisierung und Wissenstransfer',
        ];

        // Map topics by topic ID (simplified for preview)
        $topicMappingByTopicId = [
            'Q3.1' => 35,
            'Q3.2' => 36,
            'Q3.3' => 37,
            'Q3.4' => 38,
            'Q3.5' => 39,
            'Q3.6' => 40,
            'Q3.7' => 41,
            'Q3.8' => 42,
            'Q3.9' => 43,
            'Q3.10' => 44,
            'Q3.11' => 45,
            'Q3.12' => 46,
            'Q3.13' => 47,
            'Q3.14' => 48,
            'Q3.15' => 49,
            'Q3.16' => 50,
        ];
        
        // Add topic IDs for preview
        $topicEntities = [];
        foreach ($topicMappingByTopicId as $key => $topicId) {
            if (isset($data[$key]) && $data[$key]) {
                $topicEntities[] = [
                    'id' => $topicId,
                    'name' => $topicMapping[$key]
                ];
            }
        }
        $payload['topics'] = $topicEntities;
        
        // Process states (Q5.1 to Q5.9) - simplified for preview
        $stateMapping = [
            'Q5.1' => 'Burgenland',
            'Q5.2' => 'Kärnten',
            'Q5.3' => 'Niederösterreich',
            'Q5.4' => 'Oberösterreich',
            'Q5.5' => 'Salzburg',
            'Q5.6' => 'Steiermark',
            'Q5.7' => 'Tirol',
            'Q5.8' => 'Vorarlberg',
            'Q5.9' => 'Wien',
        ];

        $stateMappingByStateId = [
            'Q5.1' => '2',
            'Q5.2' => '3',
            'Q5.3' => '4',
            'Q5.4' => '5',
            'Q5.5' => '6',
            'Q5.6' => '7',
            'Q5.7' => '8',
            'Q5.8' => '1',
            'Q5.9' => '9',
        ];
        
        // Check if all states are selected (Q5.10)
        if (isset($data['Q5.10']) && $data['Q5.10']) {
            foreach ($stateMapping as $key => $stateName) {
                $payload['states'][] = [
                    'id' => $stateMappingByStateId[$key],
                    'name' => $stateName
                ];
            }
        } else {
            // Add selected states
            foreach ($stateMapping as $key => $stateName) {
                if (isset($data[$key]) && $data[$key]) {
                    $payload['states'][] = [
                        'id' => $stateMappingByStateId[$key],
                        'name' => $stateName
                    ];
                }
            }
        }
        
        
        // Initialize standard financing structure with expected IDs
        $payload['financing'] = [
            ['id' => 'costsGap', 'value' => 0],     // GAP Strategieplan
            ['id' => 'costsPrivate', 'value' => 0], // Private und Eigenmittel
            ['id' => 'costsExternal', 'value' => 0] // Andere Finanzquellen
        ];
        
        // Process financing from specific columns (AK, AL, AM) - simplified
        // Column AK = GAP Strategieplan
        if (isset($data['AK'])) {
            $value = $data['AK'];
            if (is_string($value)) {
                $value = str_replace(',', '.', $value);
            }
            $value = (float)$value;
            
            if ($value > 0) {
                $payload['financing'][0]['value'] = $value;
            }
        }
        
        // Column AL = Private und Eigenmittel
        if (isset($data['AL'])) {
            $value = $data['AL'];
            if (is_string($value)) {
                $value = str_replace(',', '.', $value);
            }
            $value = (float)$value;
            
            if ($value > 0) {
                $payload['financing'][1]['value'] = $value;
            }
        }
        
        // Column AM = Andere Finanzquellen
        if (isset($data['AM'])) {
            $value = $data['AM'];
            if (is_string($value)) {
                $value = str_replace(',', '.', $value);
            }
            $value = (float)$value;
            
            if ($value > 0) {
                $payload['financing'][2]['value'] = $value;
            }
        }
        
        // Simplified contact processing - just create a basic structure without all the details
        if (!empty($data['Q12'])) {
            $contact = [
                'firstName' => $data['Q12.1'] ?? '',
                'lastName' => $data['Q12.2'] ?? '',
                'email' => $data['Q12.3'] ?? '',
                'phone' => $data['Q12.4'] ?? '',
                'organization' => $data['Q12.5'] ?? '',
                'position' => $data['Q12.6'] ?? '',
                'isPublic' => true
            ];
            
            if (!empty($contact['firstName']) || !empty($contact['lastName']) || !empty($contact['email'])) {
                $payload['contacts'][] = $contact;
            }
        }
        
        // Process links (columns AY to BH) - simplified to include only essential info
        $linkColumns = ['AY', 'AZ', 'BA', 'BB', 'BC', 'BD', 'BE', 'BF', 'BG', 'BH'];
        
        // Process links in pairs (label + url)
        for ($i = 0; $i < count($linkColumns) - 1; $i += 2) {
            $labelColumn = $linkColumns[$i];
            $urlColumn = $linkColumns[$i + 1];
            
            // Skip if both columns are empty
            if (empty($data[$labelColumn]) && empty($data[$urlColumn])) {
                continue;
            }
            
            $label = !empty($data[$labelColumn]) ? $data[$labelColumn] : '';
            $url = !empty($data[$urlColumn]) ? $data[$urlColumn] : '';
            
            // If we have a URL in the label column and no URL in the URL column,
            // treat the label as a URL
            if (!empty($label) && empty($url) && $this->looksLikeUrl($label)) {
                $url = $label;
                $label = '';
            }
            
            // Skip if no URL is available
            if (empty($url)) {
                continue;
            }
            
            // Ensure URL has a protocol (simplified)
            if (!preg_match('~^(?:f|ht)tps?://~i', $url)) {
                $url = 'https://' . $url;
            }
            
            $payload['links'][] = [
                'url' => $url,
                'label' => $label,
                'value' => $url
            ];
        }
        
        // Process videos (columns BI to BN) - simplified to include only essential info
        $videoColumns = ['BI', 'BJ', 'BK', 'BL', 'BM', 'BN'];
        
        // Process videos in pairs (label + url)
        for ($i = 0; $i < count($videoColumns) - 1; $i += 2) {
            $labelColumn = $videoColumns[$i];
            $urlColumn = $videoColumns[$i + 1];
            
            // Skip if both columns are empty
            if (empty($data[$labelColumn]) && empty($data[$urlColumn])) {
                continue;
            }
            
            $label = !empty($data[$labelColumn]) ? $data[$labelColumn] : '';
            $url = !empty($data[$urlColumn]) ? $data[$urlColumn] : '';
            
            // If we have a URL in the label column and no URL in the URL column,
            // treat the label as a URL
            if (!empty($label) && empty($url) && $this->looksLikeUrl($label)) {
                $url = $label;
                $label = '';
            }
            
            // Skip if no URL is available
            if (empty($url)) {
                continue;
            }
            
            // Ensure URL has a protocol (simplified)
            if (!preg_match('~^(?:f|ht)tps?://~i', $url)) {
                $url = 'https://' . $url;
            }
            
            $payload['videos'][] = [
                'url' => $url,
                'label' => $label,
                'value' => $url
            ];
        }
        
        // For files and images, we'll just indicate their presence without downloading them
        // Check if file attachments are present (columns BO to CR)
        $filePairs = [
            ['BO', 'BP'], ['BQ', 'BR'], ['BS', 'BT'], ['BU', 'BV'], ['BW', 'BX'], 
            ['BY', 'BZ'], ['CA', 'CB'], ['CC', 'CD'], ['CE', 'CF'], ['CG', 'CH'], 
            ['CI', 'CJ'], ['CK', 'CL'], ['CM', 'CN'], ['CO', 'CP'], ['CQ', 'CR'],
            ['CS', 'CT'], ['CU', 'CV'], ['CW', 'CX'], ['CY', 'CZ']
        ];
        
        foreach ($filePairs as $index => $pair) {
            $filenameCol = $pair[0];
            $urlCol = $pair[1];
            
            // Skip if either filename or URL is empty
            if (empty($data[$filenameCol]) || empty($data[$urlCol])) {
                continue;
            }
            
            $filename = $data[$filenameCol];
            $url = $data[$urlCol];
            
            // Determine if it's an image or document (simplified)
            $isImage = $this->isImageFile($filename);
            $extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
            $mimeType = $this->getMimeTypeFromFilename($filename);
            
            // Create a placeholder entry for preview without actually downloading
            $fileEntry = [
                'id' => "preview_file_" . ($index + 1),
                'name' => $filename,
                'originalName' => $filename,
                'extension' => $extension,
                'mimeType' => $mimeType,
                'description' => $filename,
                'size' => 0, // Unknown size for preview
                'preview_only' => true // Mark as preview only
            ];
            
            if ($isImage) {
                $fileEntry['copyright'] = $data['DA'] ?? '';
                $payload['images'][] = $fileEntry;
            } else {
                $payload['files'][] = $fileEntry;
            }
        }
        
        return $payload;
    }
} 