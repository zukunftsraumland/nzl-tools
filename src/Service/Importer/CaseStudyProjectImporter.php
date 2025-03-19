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
    public function __construct(
        EntityManagerInterface $em,
        ProjectService $projectService,
        SluggerInterface $slugger,
        string $uploadDir
    ) {
        parent::__construct($em, $projectService, $slugger, $uploadDir);
    }

    /**
     * {@inheritdoc}
     */
    public function getName(): string
    {
        return 'CaseStudy';
    }

    /**
     * {@inheritdoc}
     */
    public function getDescription(): string
    {
        return 'Import von Projekten als Case Studies (Q2.1, etc.)';
    }

    /**
     * {@inheritdoc}
     */
    public function getType(): string
    {
        return 'casestudy';
    }

    /**
     * {@inheritdoc}
     */
    protected function prepareProjectPayload(array $data): array
    {
        // First, call the parent's method, but we'll override the file processing later
        $payload = parent::prepareProjectPayload($data);
        
        // Set the caseStudy flag to true
        $payload['caseStudy'] = true;
        
        // Debug the data array to see what keys are available
        
        // Map fields according to the Excel header names (Q21, Q22, etc.)
        // These names should match exactly what's in row 4 of the Excel file
        
        // Map and log each field for debugging
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
        
        
        // Assign them to the payload
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
        
        $columnValues = [];
        foreach ($columnMappings as $column => $field) {
            if (isset($data[$column]) && !empty($data[$column])) {
                $payload[$field] = $data[$column];
                $columnValues[$column] = $data[$column];
            }
        }
        
        
        // Clear any file/image entries that might have been added by the parent's processFileAttachmentsFromExcel
        // This is necessary because the parent method may have interpreted these columns as file attachments
        $payload['files'] = [];
        $payload['images'] = [];
        
        // Clear any tags processed by the parent (StandardProjectImporter)
        $payload['tags'] = [];
        
        // Process tags with our special case study logic
        if (!empty($data['Q4']) || isset($data['U'])) {
            $keywords = $data['Q4'] ?? $data['U'] ?? '';
            $allKeywords = explode(',', $keywords);

            // Process keywords and convert them to tags
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
            // $this->processTagsForCaseStudy($keywords, $payload);
        }
        
        // Process synergy fund tags and synergy goal tags
        $this->processSynergyTags($data, $payload);
        
        // Process files from columns CJ onwards (if implementation is needed)
        $this->processCaseStudyFileAttachments($data, $payload);
        
        // Extract localWorkgroupId from column AG and map to name
        if (isset($data['AG']) && is_numeric($data['AG'])) {
            $localWorkgroupId = (int)$data['AG'];
            $payload['localWorkgroupId'] = $localWorkgroupId;
            
            // Get the LocalWorkgroup name from mapping
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
        
        // Add LE category name without DB lookup
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
     * Overrides the file attachment processing from StandardProjectImporter
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
        
        // Initialize arrays to track existing file IDs
        $existingImageIds = [];
        $existingFileIds = [];
        
        // First populate existing IDs from payload if they exist
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
        
        // Process regular file attachment (BO/BP)
        if (!empty($data['BO']) && !empty($data['BP'])) {
            $filename = $data['BO'];
            $url = $data['BP'];
            
            
            try {
                $fileData = $this->downloadAttachmentFromUrl($url, $filename);
                
                if ($fileData) {
                    // Check if this file ID already exists in our payload
                    if (!in_array($fileData['id'], $existingFileIds)) {
                        // Add to files array
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
                
            }
        }
        
        // Process image attachment (BQ/BR)
        if (!empty($data['BQ']) && !empty($data['BR'])) {
            $filename = $data['BQ'];
            $url = $data['BR'];
            
            
            try {
                $fileData = $this->downloadAttachmentFromUrl($url, $filename);
                
                if ($fileData) {
                    // For images, we need to determine if it's actually an image
                    $isImage = $this->isImageFile($filename);
                    
                    if ($isImage) {
                        // Check if this image ID already exists in our payload
                        if (!in_array($fileData['id'], $existingImageIds)) {
                            // Add to images array
                            $payload['images'][] = [
                                'id' => $fileData['id'],
                                'name' => $fileData['name'],
                                'extension' => $fileData['extension'],
                                'mimeType' => $fileData['mimeType'],
                                'copyright' => '',
                                'description' => $fileData['name'] ?? ''
                            ];
                            
                            
                        }
                    } else {
                        // If it's not an image but in the image column, we'll treat it as a regular file
                        if (!in_array($fileData['id'], $existingFileIds)) {
                            $payload['files'][] = [
                                'id' => $fileData['id'],
                                'name' => $fileData['name'],
                                'extension' => $fileData['extension'],
                                'mimeType' => $fileData['mimeType'],
                                'description' => $fileData['name'] ?? '',
                            ];
                            

                        }
                    }
                }
            } catch (\Exception $e) {
                
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
            
            // Sanitize the filename
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
                return null;
            }
            
            // Check if we got an empty response
            if (empty($fileContents)) {
                return null;
            }
            
            // Get file information
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
     * {@inheritdoc}
     */
    public function processImportItem(ProjectImport $import, int $rowIndex): array
    {
        try {
            // Call the parent method to get the basic result
            $result = parent::processImportItem($import, $rowIndex);
            
            // If the basic processing failed, just return the result
            if ($result['status'] === 'error') {
                return $result;
            }
            
            // Log the column headers to help debug field mapping
            $filePath = $import->getFilePath();
            
            // Check if the file path is already absolute
            if (!file_exists($filePath)) {
                // If not absolute, prepend the upload directory
                $filePath = $this->uploadDir . '/' . $filePath;
            }
            
            // This will only be done on the first row to avoid repeated logging
            if ($rowIndex === 1) {
                $this->logExcelColumnHeaders($filePath);
            }
            
            // Set the caseStudy flag to true in the result
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
     * Log the column headers from the Excel file for debugging purposes
     *
     * @param string $filePath The path to the Excel file
     */
    private function logExcelColumnHeaders(string $filePath): void
    {
        try {
            // Load the spreadsheet
            $spreadsheet = IOFactory::load($filePath);
            $worksheet = $spreadsheet->getActiveSheet();
            
            // Get the highest column index
            $highestColumnIndex = Coordinate::columnIndexFromString($worksheet->getHighestColumn());
            
            // Extract headers from row 4 (field names)
            $headers = [];
            for ($col = 1; $col <= $highestColumnIndex; $col++) {
                $columnLetter = Coordinate::stringFromColumnIndex($col);
                $fieldName = $worksheet->getCellByColumnAndRow($col, 4)->getValue();
                if (!empty($fieldName)) {
                    $headers[$columnLetter] = $fieldName;
                }
            }
            
            
        } catch (\Exception $e) {
            
        }
    }
    
    /**
     * Override the generatePreview method to create a more efficient version for case studies
     * 
     * This optimized version skips expensive operations like file downloading and database lookups
     * during preview generation to prevent timeouts on the live server.
     * 
     * {@inheritdoc}
     */
    public function generatePreview(ProjectImport $import): array
    {
        $results = [];
        
        try {
            $filePath = $import->getFilePath();
            
            // Check if the file path is already absolute
            if (!file_exists($filePath)) {
                // If not absolute, prepend the upload directory
                $filePath = $this->uploadDir . '/' . $filePath;
            }
            
            // Load the spreadsheet
            $spreadsheet = IOFactory::load($filePath);
            $worksheet = $spreadsheet->getActiveSheet();
            
            // Get the header row index
            $headerRowIndex = $this->getHeaderRowCount();
            
            // Get the highest column and row
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
            
            // Process the rows to generate preview
            $maxPreviewRows = 100; // Limit the number of rows for preview
            $rowLimit = min($highestRow, $maxPreviewRows + $headerRowIndex);
            
            for ($rowIndex = $headerRowIndex + 1; $rowIndex <= $rowLimit; $rowIndex++) {
                // Extract data from the row
                $rowData = [];
                
                for ($col = 1; $col <= $highestColumnIndex; $col++) {
                    $columnLetter = Coordinate::stringFromColumnIndex($col);
                    $value = $worksheet->getCellByColumnAndRow($col, $rowIndex)->getValue();
                    
                    // Map cell data
                    if (isset($headerMapping[$columnLetter])) {
                        $rowData[$headerMapping[$columnLetter]] = $value;
                    }
                    
                    // Also store data by column letter for direct access
                    $rowData[$columnLetter] = $value;
                }
                
                // Skip empty rows
                if (empty($rowData)) {
                    continue;
                }
                
                // Prepare basic payload without expensive operations
                $payload = $this->preparePreviewPayload($rowData);
                
                // Add basic case study fields to payload (without database lookups)
                $payload['caseStudy'] = true;
                
                // Map the essential fields needed for preview
                $previewItem = [
                    'rowNumber' => $rowIndex - $headerRowIndex,
                    'title' => $payload['title'] ?? '',
                    'description' => $payload['description'] ?? '',
                    'projectCode' => $payload['projectCode'] ?? '',
                    'startDate' => $payload['startDate'] ?? null,
                    'endDate' => $payload['endDate'] ?? null,
                    'status' => 'valid', // Default to valid for preview
                    'payload' => $payload
                ];
                
                // Basic validation for preview (minimal)
                if (empty($previewItem['title'])) {
                    $previewItem['status'] = 'warning';
                    $previewItem['message'] = 'Projekt hat keinen Titel';
                }
                
                // Check if project with the same title already exists
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
        
        // Include basic case study fields without DB lookups
        // Map case study specific fields from columns BV-CI to improve preview data
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
        
        // For preview only, include placeholder tags without DB lookups
        if (!empty($data['Q4']) || isset($data['U'])) {
            $keywords = $data['Q4'] ?? $data['U'] ?? '';
            $allKeywords = explode(',', $keywords);

            // Process keywords and convert them to tags
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
        
        // For synergy tags, just indicate their presence in preview without DB lookups
        $synergyFundTagsPresent = false;
        $synergyGoalTagsPresent = false;
        
        // Check for synergy fund tags (columns CL-CP)
        foreach (['CL', 'CM', 'CN', 'CO', 'CP'] as $column) {
            if (isset($data[$column]) && $data[$column] == 1) {
                $synergyFundTagsPresent = true;
                break;
            }
        }
        
        // Check for synergy goal tags (columns CS-CY)
        foreach (['CS', 'CT', 'CU', 'CV', 'CW', 'CX', 'CY'] as $column) {
            if (isset($data[$column]) && $data[$column] == 1) {
                $synergyGoalTagsPresent = true;
                break;
            }
        }
        
        // Add placeholder indicators for synergy tags
        if ($synergyFundTagsPresent) {
            $payload['hasSynergyFundTags'] = true;
        }
        
        if ($synergyGoalTagsPresent) {
            $payload['hasSynergyGoalTags'] = true;
        }
        
        // Extract localWorkgroupId from column AG and map to name
        if (isset($data['AG']) && is_numeric($data['AG'])) {
            $localWorkgroupId = (int)$data['AG'];
            $payload['localWorkgroupId'] = $localWorkgroupId;
            
            // Get the LocalWorkgroup name from mapping
            $localWorkgroupNameMapping = $this->getLocalWorkgroupNameMapping();
            if (isset($localWorkgroupNameMapping[$localWorkgroupId])) {
                $payload['localWorkgroupName'] = $localWorkgroupNameMapping[$localWorkgroupId];
            }
        }
        
        // Add LE category name without DB lookup
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