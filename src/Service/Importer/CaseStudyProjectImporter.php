<?php

namespace App\Service\Importer;

use App\Entity\ProjectImport;
use App\Service\ProjectService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\String\Slugger\SluggerInterface;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;

/**
 * Case Study Project Importer
 * 
 * This importer handles the case study project import format, which is similar to the standard format
 * but sets the caseStudy flag to true and has additional case study specific fields.
 * 
 * The main difference from the standard importer is that columns BV-CI are used for case study 
 * specific fields rather than file attachments. Files in case study imports begin at column CJ.
 */
class CaseStudyProjectImporter extends StandardProjectImporter
{
    /**
     * Constructor
     */
    public function __construct(
        EntityManagerInterface $em,
        ProjectService $projectService,
        SluggerInterface $slugger,
        string $uploadDir
    ) {
        parent::__construct($em, $projectService, $slugger, $uploadDir);
    }

    /**
     * Get the name of this importer
     *
     * @return string Importer name
     */
    public function getName(): string
    {
        return 'CaseStudy';
    }

    /**
     * Get the description of this importer
     *
     * @return string Importer description
     */
    public function getDescription(): string
    {
        return 'Import von Projekten als Case Studies (Q2.1, etc.)';
    }

    /**
     * Get the type identifier for this importer
     *
     * @return string Importer type
     */
    public function getType(): string
    {
        return 'casestudy';
    }

    /**
     * Prepare the project payload data from Excel import for Case Study
     *
     * Extends the standard project import with additional case study specific fields.
     * Processes case study fields from columns BV-CI, sets the caseStudy flag to true,
     * and handles special tag processing for case studies.
     *
     * @param array $data Raw data from Excel import
     * @return array Processed project payload
     */
    protected function prepareProjectPayload(array $data): array
    {
        $payload = parent::prepareProjectPayload($data);
        $payload['caseStudy'] = true;
        
        // Map fields according to the Excel header names (Q21, Q22, etc.)
        $caseStudyFields = [
            'exemplary' => $data['Q21'] ?? null,
            'initialContext' => $data['Q22'] ?? null,
            'initialContextGoals' => $data['Q23'] ?? null,
            'fundingMethod' => $data['Q24'] ?? null,
            'fundingMethodStakeholders' => $data['Q25'] ?? null,
            'resultsQuantity' => $data['Q26'] ?? null,
            'resultsQuality' => $data['Q27'] ?? null,
            'innovations' => $data['Q28'] ?? null,
            'additionalValue' => $data['Q29'] ?? null,
            'integrationYoungCitizen' => $data['Q30'] ?? null,
            'integrationFemaleCitizen' => $data['Q31'] ?? null,
            'integrationMinorities' => $data['Q32'] ?? null,
            'learningExperience' => $data['Q33'] ?? null,
            'transferable' => $data['Q34'] ?? null,
        ];
        
        foreach ($caseStudyFields as $field => $value) {
            if (!empty($value)) {
                $payload[$field] = $value;
            }
        }
        
        // Also try to access by column letters, in case that's how they're stored
        $columnMappings = [
            'BV' => 'exemplary',
            'BW' => 'initialContext',
            'BX' => 'initialContextGoals',
            'BY' => 'fundingMethod',
            'BZ' => 'fundingMethodStakeholders',
            'CA' => 'resultsQuantity',
            'CB' => 'resultsQuality',
            'CC' => 'innovations',
            'CD' => 'additionalValue',
            'CE' => 'integrationYoungCitizen',
            'CF' => 'integrationFemaleCitizen',
            'CG' => 'integrationMinorities',
            'CH' => 'learningExperience',
            'CI' => 'transferable',
        ];
        
        foreach ($columnMappings as $column => $field) {
            if (isset($data[$column]) && !empty($data[$column])) {
                $payload[$field] = $data[$column];
            }
        }
        
        // Clear any data from parent that we need to override
        $payload['files'] = [];
        $payload['images'] = [];
        $payload['tags'] = [];
        
        // Process tags with case study logic
        if (!empty($data['Q4']) || isset($data['U'])) {
            $keywords = $data['Q4'] ?? $data['U'] ?? '';
            $allKeywords = explode(',', $keywords);

            if (!empty($allKeywords)) {
                foreach ($allKeywords as $keyword) {
                    // TODO: Check if its actually a keyword and not a text because the data coming from the export is not always clean. 
                    $keyword = trim($keyword);
                    if (!empty($keyword)) {
                        $payload['tags'][] = [
                            'name' => $keyword,
                            'context' => 'tag'
                        ];
                    }
                }
            }
        }
        
        $this->processSynergyTags($data, $payload);
        $this->processCaseStudyFileAttachments($data, $payload);
        
        // Extract localWorkgroupId and map to name
        if (isset($data['AG']) && is_numeric($data['AG'])) {
            $localWorkgroupId = (int)$data['AG'];
            $payload['localWorkgroupId'] = $localWorkgroupId;
            
            $localWorkgroupNameMapping = $this->getLocalWorkgroupNameMapping();
            if (isset($localWorkgroupNameMapping[$localWorkgroupId])) {
                $payload['localWorkgroupName'] = $localWorkgroupNameMapping[$localWorkgroupId];
            }
        }

        if(isset($data['Q8.2']) && $data['Q8.2'] == 1) {
            $payload['cooperationProjectAt'] = true;
        }

        if(isset($data['Q8.3']) && $data['Q8.3'] == 1) {
            $payload['cooperationProjectEu'] = true;
        }
        
        if (!empty($data['Q3.7']) || !empty($data['K'])) {
            $payload['leFundingCategoryName'] = $data['Q3.7'] ?? $data['K'] ?? '';
        }
        
        return $payload;
    }
    
   
    /**
     * Process synergy fund tags and synergy goal tags from Excel data
     * 
     * Processes:
     * - synergyFundTags from columns CL-CP
     * - synergyGoalTags from columns CS-CY
     * 
     * If the value in a column is 1, the corresponding tag is added
     * to the project's synergyFundTags or synergyGoalTags collection.
     *
     * @param array $data The Excel data
     * @param array &$payload The project payload to update
     */
    private function processSynergyTags(array $data, array &$payload): void
    {
        // Initialize arrays in the payload
        $payload['synergyFundTags'] = [];
        $payload['synergyGoalTags'] = [];
        
        // Process synergyFundTags (columns CL-CP)
        $synergyFundTagMappings = [
            'CL' => ['id' => 37, 'name' => 'Europäischer Sozialfonds ESF+'],
            'CM' => ['id' => 38, 'name' => 'Europäischer Fonds für Regionalentwicklung IBW/EFRE'],
            'CN' => ['id' => 39, 'name' => 'INTERREG'],
            'CO' => ['id' => 40, 'name' => 'Europäischer Meeres-, Fischerei- und Aquakulturfonds EMFAF'],
            'CP' => ['id' => 41, 'name' => 'Fonds für einen gerechten Übergang JTF'],
        ];
        
        foreach ($synergyFundTagMappings as $column => $tagInfo) {
            // Check if the column exists and has a value of 1
            if (isset($data[$column]) && $data[$column] == 1) {
                
                // First try to find the tag by ID
                $tag = $this->em->getRepository(\App\Entity\Tag::class)->find($tagInfo['id']);
                
                // If not found by ID, try to find by name and context
                if (!$tag) {
                    $tag = $this->em->getRepository(\App\Entity\Tag::class)->findOneBy([
                        'name' => $tagInfo['name'],
                        'context' => 'synergyFundTag'
                    ]);
                }
                
                // If tag is found, add it to the payload
                if ($tag) {
                    $payload['synergyFundTags'][] = [
                        'id' => $tag->getId(),
                        'name' => $tag->getName(),
                        'context' => 'synergyFundTag'
                    ];
                }
            }
        }
        
        // Process synergyGoalTags (columns CS-CY)
        $synergyGoalTagMappings = [
            'CS' => ['id' => 43, 'name' => 'Langzeitvision für ländliche Gebiete in Europa bis 2040 (EU Long Term Vision)'],
            'CT' => ['id' => 44, 'name' => 'EU Biodiversitätsstrategie 2023'],
            'CU' => ['id' => 45, 'name' => 'Vom Hof auf den Tisch (Farm to Fork Strategie)'],
            'CV' => ['id' => 46, 'name' => 'EU Digitalisierungsstrategie'],
            'CW' => ['id' => 47, 'name' => 'EU KMU-Strategie'],
            'CX' => ['id' => 48, 'name' => 'EU Strategie für die Gleichstellung der Geschlechter'],
            'CY' => ['id' => 49, 'name' => 'UN-Nachhaltigkeitsziele SDG'],
        ];
        
        foreach ($synergyGoalTagMappings as $column => $tagInfo) {
            // Check if the column exists and has a value of 1
            if (isset($data[$column]) && $data[$column] == 1) {
                
                // First try to find the tag by ID
                $tag = $this->em->getRepository(\App\Entity\Tag::class)->find($tagInfo['id']);
                
                // If not found by ID, try to find by name and context
                if (!$tag) {
                    $tag = $this->em->getRepository(\App\Entity\Tag::class)->findOneBy([
                        'name' => $tagInfo['name'],
                        'context' => 'synergyGoalTag'
                    ]);
                }
                
                // If tag is found, add it to the payload
                if ($tag) {
                    $payload['synergyGoalTags'][] = [
                        'id' => $tag->getId(),
                        'name' => $tag->getName(),
                        'context' => 'synergyGoalTag'
                    ];
                }
            }
        }
    }
    
    /**
     * Override parent's file attachment processing with empty implementation
     * 
     * Since columns BV-CI are used for case study fields rather than file attachments,
     * we need to override this method to prevent it from processing those columns as files.
     * 
     * @param array $data The Excel data
     * @param array &$payload The project payload to update
     */
    protected function processFileAttachmentsFromExcel(array $data, array &$payload): void
    {
        // This method is intentionally empty to prevent the parent's implementation
        // from processing columns BV-CI as file attachments
        // The actual file processing for case studies will be done in processCaseStudyFileAttachments
    }
    
    /**
     * Process file attachments for case study imports
     * 
     * Handles file attachments for case study imports from columns:
     * - BO (file label) and BP (file URL)
     * - BQ (image label) and BR (image URL)
     * 
     * @param array $data The Excel data
     * @param array &$payload The project payload to update
     */
    private function processCaseStudyFileAttachments(array $data, array &$payload): void
    {
        $existingImageIds = [];
        $existingFileIds = [];
        
        if (isset($payload['images']) && is_array($payload['images'])) {
            foreach ($payload['images'] as $image) {
                if (isset($image['id'])) {
                    $existingImageIds[] = $image['id'];
                }
            }
        }
        
        if (isset($payload['files']) && is_array($payload['files'])) {
            foreach ($payload['files'] as $file) {
                if (isset($file['id'])) {
                    $existingFileIds[] = $file['id'];
                }
            }
        }
        
        // Regular file attachment (BO/BP)
        if (!empty($data['BO']) && !empty($data['BP'])) {
            $filename = $data['BO'];
            $url = $data['BP'];
            
            try {
                $fileData = $this->downloadAttachmentFromUrl($url, $filename);
                
                if ($fileData && !in_array($fileData['id'], $existingFileIds)) {
                    $payload['files'][] = [
                        'id' => $fileData['id'],
                        'name' => $fileData['name'],
                        'extension' => $fileData['extension'],
                        'mimeType' => $fileData['mimeType'],
                        'description' => $fileData['name'] ?? '',
                    ];
                }
            } catch (\Exception $e) {
                // Silent exception handling
            }
        }
        
        // Image attachment (BQ/BR)
        if (!empty($data['BQ']) && !empty($data['BR'])) {
            $filename = $data['BQ'];
            $url = $data['BR'];
            
            try {
                $fileData = $this->downloadAttachmentFromUrl($url, $filename);
                
                if ($fileData) {
                    $isImage = $this->isImageFile($filename);
                    
                    if ($isImage && !in_array($fileData['id'], $existingImageIds)) {
                        $payload['images'][] = [
                            'id' => $fileData['id'],
                            'name' => $fileData['name'],
                            'extension' => $fileData['extension'],
                            'mimeType' => $fileData['mimeType'],
                            'copyright' => '',
                            'description' => $fileData['name'] ?? ''
                        ];
                    } else if (!$isImage && !in_array($fileData['id'], $existingFileIds)) {
                        $payload['files'][] = [
                            'id' => $fileData['id'],
                            'name' => $fileData['name'],
                            'extension' => $fileData['extension'],
                            'mimeType' => $fileData['mimeType'],
                            'description' => $fileData['name'] ?? '',
                        ];
                    }
                }
            } catch (\Exception $e) {
                // Silent exception handling
            }
        }
    }
    
    /**
     * Downloads a file from a URL and creates a File entity
     * 
     * This is a reimplementation of the private downloadFileFromUrl method
     * from StandardProjectImporter, which cannot be accessed directly.
     * 
     * @param string $url The URL to download from
     * @param string $filename The filename to save as
     * @return array|null The file data or null if download failed
     */
    private function downloadAttachmentFromUrl(string $url, string $filename): ?array
    {
        try {
            // Ensure URL has a protocol
            if (!preg_match('~^(?:f|ht)tps?://~i', $url)) {
                $url = 'https://' . $url;
            }
            
            // URL encode special characters while preserving structure
            $urlParts = parse_url($url);
            if (isset($urlParts['path'])) {
                $encodedPath = implode('/', array_map('rawurlencode', explode('/', $urlParts['path'])));
                
                $scheme = isset($urlParts['scheme']) ? $urlParts['scheme'] . '://' : 'https://';
                $host = $urlParts['host'] ?? '';
                $port = isset($urlParts['port']) ? ':' . $urlParts['port'] : '';
                $query = isset($urlParts['query']) ? '?' . $urlParts['query'] : '';
                $fragment = isset($urlParts['fragment']) ? '#' . $urlParts['fragment'] : '';
                
                $url = $scheme . $host . $port . $encodedPath . $query . $fragment;
            }
            
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
            
            // Attempt to download with retries
            $fileContents = null;
            $attempts = 0;
            $maxAttempts = 3;
            
            while ($attempts < $maxAttempts) {
                $attempts++;
                
                try {
                    $fileContents = @file_get_contents($url, false, $context);
                    if ($fileContents !== false) {
                        break; 
                    }
                    
                    if ($attempts < $maxAttempts) {
                        sleep(1);
                    }
                } catch (\Exception $e) {
                    if ($attempts < $maxAttempts) {
                        sleep(1);
                    }
                }
            }
            
            if ($fileContents === false || $fileContents === null || empty($fileContents)) {
                return null;
            }
            
            $isImage = $this->isImageFile($filename);
            $extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
            $mimeType = $this->getMimeTypeFromFilename($filename);
            
            // Create file entity
            $base64Data = 'data:' . $mimeType . ';base64,' . base64_encode($fileContents);
            
            $file = new \App\Entity\File();
            $file
                ->setName($cleanFilename)
                ->setCreatedAt(new \DateTime())
                ->setData($base64Data)
                ->setHash(md5($base64Data))
                ->setMimeType($mimeType)
                ->setExtension($extension);
            
            // Reuse existing file if hash matches
            $existingFile = $this->em->getRepository(\App\Entity\File::class)->findOneBy([
                'hash' => $file->getHash(),
            ]);
            
            if (!$existingFile) {
                $this->em->persist($file);
                $this->em->flush();
            } else {
                $file = $existingFile;
            }
            
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
     * This is a copy of the private sanitizeFilename method from StandardProjectImporter.
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
        
        // Transliterate the basename to ASCII
        $basename = $this->transliterateString($basename);
        
        // Remove any remaining non-alphanumeric characters except those that are safe
        $basename = preg_replace('/[^a-zA-Z0-9_\-\.]/', '_', $basename);
        
        // Ensure we don't have multiple underscores in a row
        $basename = preg_replace('/_+/', '_', $basename);
        
        // Ensure we don't have underscores at the beginning or end
        $basename = trim($basename, '_');
        
        // If basename ends up empty, use a generic name
        if (empty($basename)) {
            $basename = 'file';
        }
        
        // Put it back together
        return $basename . (empty($extension) ? '' : '.' . $extension);
    }
    
    /**
     * Transliterates a string to ASCII
     * 
     * This is a copy of the private transliterateString method from StandardProjectImporter.
     * 
     * @param string $string The string to transliterate
     * @return string The transliterated string
     */
    private function transliterateString(string $string): string
    {
        // Convert to ASCII
        $string = transliterator_transliterate('Any-Latin; Latin-ASCII', $string);
        
        // Additional transliterations for characters that might be missed
        $charsFrom = ['ä', 'ö', 'ü', 'Ä', 'Ö', 'Ü', 'ß', 'é', 'è', 'ê', 'ë', 'É', 'È', 'Ê', 'Ë', 'á', 'à', 'â', 'ã', 'å', 'Á', 'À', 'Â', 'Ã', 'Å', 'ó', 'ò', 'ô', 'õ', 'Ó', 'Ò', 'Ô', 'Õ', 'í', 'ì', 'î', 'ï', 'Í', 'Ì', 'Î', 'Ï', 'ú', 'ù', 'û', 'ñ', 'Ñ', 'ç', 'Ç'];
        $charsTo   = ['ae', 'oe', 'ue', 'Ae', 'Oe', 'Ue', 'ss', 'e', 'e', 'e', 'e', 'E', 'E', 'E', 'E', 'a', 'a', 'a', 'a', 'a', 'A', 'A', 'A', 'A', 'A', 'o', 'o', 'o', 'o', 'O', 'O', 'O', 'O', 'i', 'i', 'i', 'i', 'I', 'I', 'I', 'I', 'u', 'u', 'u', 'n', 'N', 'c', 'C'];
        
        return str_replace($charsFrom, $charsTo, $string);
    }

    /**
     * Process an import row from the Excel file
     * 
     * Extends the parent's implementation to add case study specific handling,
     * ensuring the caseStudy flag is properly set to true.
     *
     * @param ProjectImport $import The import object
     * @param int $rowIndex The row index to process
     * @return array Result with status, payload, and any error messages
     */
    public function processImportItem(ProjectImport $import, int $rowIndex): array
    {
        try {
            $result = parent::processImportItem($import, $rowIndex);
            
            if ($result['status'] === 'error') {
                return $result;
            }
            
            if (isset($result['payload'])) {
                $result['payload']['caseStudy'] = true;
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
     * Override the generatePreview method to create a more efficient version for case studies
     * 
     * This optimized version skips expensive operations like file downloading and database lookups
     * during preview generation to prevent timeouts on the live server.
     * 
     * @param ProjectImport $import The import object
     * @return array Preview data with simplified project information
     */
    public function generatePreview(ProjectImport $import): array
    {
        $results = [];
        
        try {
            $filePath = $import->getFilePath();
            
            if (!file_exists($filePath)) {
                $filePath = $this->uploadDir . '/' . $filePath;
            }
            
            $spreadsheet = IOFactory::load($filePath);
            $worksheet = $spreadsheet->getActiveSheet();
            
            $headerRowIndex = $this->getHeaderRowCount();
            $highestColumnIndex = Coordinate::columnIndexFromString($worksheet->getHighestColumn());
            $highestRow = $worksheet->getHighestRow();
            
            // Build header mapping from row 4 (field names)
            $headerMapping = [];
            
            for ($col = 1; $col <= $highestColumnIndex; $col++) {
                $columnLetter = Coordinate::stringFromColumnIndex($col);
                $fieldName = $worksheet->getCellByColumnAndRow($col, $headerRowIndex)->getValue();
                if (!empty($fieldName)) {
                    $headerMapping[$columnLetter] = $fieldName;
                }
            }
            
            // Process limited number of rows for preview
            $maxPreviewRows = 100;
            $rowLimit = min($highestRow, $maxPreviewRows + $headerRowIndex);
            
            for ($rowIndex = $headerRowIndex + 1; $rowIndex <= $rowLimit; $rowIndex++) {
                $rowData = [];
                
                for ($col = 1; $col <= $highestColumnIndex; $col++) {
                    $columnLetter = Coordinate::stringFromColumnIndex($col);
                    $value = $worksheet->getCellByColumnAndRow($col, $rowIndex)->getValue();
                    
                    if (isset($headerMapping[$columnLetter])) {
                        $rowData[$headerMapping[$columnLetter]] = $value;
                    }
                    
                    $rowData[$columnLetter] = $value;
                }
                
                if (empty($rowData)) {
                    continue;
                }
                
                $payload = $this->preparePreviewPayload($rowData);
                $payload['caseStudy'] = true;
                
                $previewItem = [
                    'rowNumber' => $rowIndex - $headerRowIndex,
                    'title' => $payload['title'] ?? '',
                    'description' => $payload['description'] ?? '',
                    'projectCode' => $payload['projectCode'] ?? '',
                    'startDate' => $payload['startDate'] ?? null,
                    'endDate' => $payload['endDate'] ?? null,
                    'status' => 'valid',
                    'payload' => $payload
                ];
                
                // Basic validation
                if (empty($previewItem['title'])) {
                    $previewItem['status'] = 'warning';
                    $previewItem['message'] = 'Projekt hat keinen Titel';
                }
                
                if (!empty($previewItem['title'])) {
                    $existingProject = $this->findProjectByTitle($previewItem['title']);
                    if ($existingProject) {
                        $previewItem['status'] = 'warning';
                        $previewItem['message'] = 'Projekt mit diesem Namen existiert bereits';
                    }
                }
                
                $results[] = $previewItem;
            }
            
            return $results;
        } catch (\Exception $e) {
            return [
                [
                    'rowNumber' => 1,
                    'title' => 'Fehler beim Generieren der Vorschau',
                    'description' => 'Es ist ein Fehler aufgetreten: ' . $e->getMessage(),
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
        $payload = [];
        
        // Basic project fields
        $payload['title'] = $data['title'] ?? $data['Q2.1'] ?? $data['A'] ?? '';
        $payload['description'] = $data['description'] ?? $data['Q11'] ?? $data['AN'] ?? '';
        $payload['projectCode'] = $data['projectCode'] ?? $data['Q2.2'] ?? $data['B'] ?? '';
        
        // Parse dates
        if (!empty($data['Q2.3']) || !empty($data['C'])) {
            $startDateValue = $data['Q2.3'] ?? $data['C'] ?? null;
            if ($startDateValue) {
                if ($startDateValue instanceof \DateTime) {
                    $payload['startDate'] = $startDateValue->format('Y-m-d');
                } else if (is_numeric($startDateValue)) {
                    $payload['startDate'] = \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($startDateValue)->format('Y-m-d');
                } else {
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
                    $payload['endDate'] = \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($endDateValue)->format('Y-m-d');
                } else {
                    try {
                        $payload['endDate'] = (new \DateTime($endDateValue))->format('Y-m-d');
                    } catch (\Exception $e) {
                        // Ignore date parsing errors for preview
                    }
                }
            }
        }
        
        // Case study specific fields
        $caseStudyFields = [
            'exemplary' => $data['Q21'] ?? $data['BV'] ?? null,
            'initialContext' => $data['Q22'] ?? $data['BW'] ?? null,
            'initialContextGoals' => $data['Q23'] ?? $data['BX'] ?? null,
            'fundingMethod' => $data['Q24'] ?? $data['BY'] ?? null,
            'fundingMethodStakeholders' => $data['Q25'] ?? $data['BZ'] ?? null,
            'resultsQuantity' => $data['Q26'] ?? $data['CA'] ?? null,
            'resultsQuality' => $data['Q27'] ?? $data['CB'] ?? null,
            'innovations' => $data['Q28'] ?? $data['CC'] ?? null,
            'additionalValue' => $data['Q29'] ?? $data['CD'] ?? null,
            'integrationYoungCitizen' => $data['Q30'] ?? $data['CE'] ?? null,
            'integrationFemaleCitizen' => $data['Q31'] ?? $data['CF'] ?? null,
            'integrationMinorities' => $data['Q32'] ?? $data['CG'] ?? null,
            'learningExperience' => $data['Q33'] ?? $data['CH'] ?? null,
            'transferable' => $data['Q34'] ?? $data['CI'] ?? null,
        ];
        
        foreach ($caseStudyFields as $field => $value) {
            if (!empty($value)) {
                $payload[$field] = $value;
            }
        }
        
        // Process tags for preview
        if (!empty($data['Q4']) || isset($data['U'])) {
            $keywords = $data['Q4'] ?? $data['U'] ?? '';
            $allKeywords = explode(',', $keywords);

            if (!empty($allKeywords)) {
                foreach ($allKeywords as $keyword) {
                    $keyword = trim($keyword);
                    if (!empty($keyword)) {
                        $payload['tags'][] = [
                            'name' => $keyword,
                            'context' => 'tag'
                        ];
                    }
                }
            }
        }
        
        // Check for synergy tags
        $synergyFundTagsPresent = false;
        $synergyGoalTagsPresent = false;
        
        foreach (['CL', 'CM', 'CN', 'CO', 'CP'] as $column) {
            if (isset($data[$column]) && $data[$column] == 1) {
                $synergyFundTagsPresent = true;
                break;
            }
        }
        
        foreach (['CS', 'CT', 'CU', 'CV', 'CW', 'CX', 'CY'] as $column) {
            if (isset($data[$column]) && $data[$column] == 1) {
                $synergyGoalTagsPresent = true;
                break;
            }
        }
        
        if ($synergyFundTagsPresent) {
            $payload['hasSynergyFundTags'] = true;
        }
        
        if ($synergyGoalTagsPresent) {
            $payload['hasSynergyGoalTags'] = true;
        }
        
        // Add localWorkgroup info
        if (isset($data['AG']) && is_numeric($data['AG'])) {
            $localWorkgroupId = (int)$data['AG'];
            $payload['localWorkgroupId'] = $localWorkgroupId;
            
            $localWorkgroupNameMapping = $this->getLocalWorkgroupNameMapping();
            if (isset($localWorkgroupNameMapping[$localWorkgroupId])) {
                $payload['localWorkgroupName'] = $localWorkgroupNameMapping[$localWorkgroupId];
            }
        }
        
        // Add LE category name
        if (!empty($data['Q6']) || !empty($data['AF'])) {
            $excelCategoryId = $data['Q6'] ?? $data['AF'] ?? null;
            $leCategoryNameMapping = $this->getLeCategoryNameMapping();
            if (isset($leCategoryNameMapping[$excelCategoryId])) {
                $payload['leFundingCategoryName'] = $leCategoryNameMapping[$excelCategoryId];
            }
        }
        
        return $payload;
    }

} 