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
        return 'CaseStudy Projektimport';
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
        error_log('Case Study Import Data Keys: ' . print_r(array_keys($data), true));
        
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
        
        // Log the fields we found
        error_log('Case Study Fields Found: ' . print_r(array_filter($caseStudyFields), true));
        
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
        
        // Log any column values we found
        if (!empty($columnValues)) {
            error_log('Case Study Column Values Found: ' . print_r($columnValues, true));
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
            $this->processTagsForCaseStudy($keywords, $payload);
        }
        
        // Process synergy fund tags and synergy goal tags
        $this->processSynergyTags($data, $payload);
        
        // Process files from columns CJ onwards (if implementation is needed)
        $this->processCaseStudyFileAttachments($data, $payload);
        
        return $payload;
    }
    
    /**
     * Process tags for case study imports
     * 
     * Unlike standard imports which split tags by comma, case study imports
     * split tags by space, except for certain special multi-word tags that
     * should be preserved as a single tag.
     * 
     * @param string $keywords The keywords string from column U / Q4
     * @param array &$payload The project payload to update
     */
    protected function processTagsForCaseStudy(string $keywords, array &$payload): void
    {
        if (empty($keywords)) {
            return;
        }
        
        // Log the input for debugging
        error_log('Case Study Tags - Processing keywords: ' . $keywords);
        
        // Define special multi-word tags that should not be split
        $specialTags = [
            'Demographischer Wandel',
            'Ganzheitliches Lernen',
            'Wald und Erlebnispädagogik'
        ];
        
        // Extract special tags first
        $remainingKeywords = $keywords;
        $extractedTags = [];
        
        foreach ($specialTags as $specialTag) {
            if (strpos($remainingKeywords, $specialTag) !== false) {
                // Add the special tag
                $extractedTags[] = $specialTag;
                
                // Remove it from the remaining keywords
                $remainingKeywords = str_replace($specialTag, '', $remainingKeywords);
                
                // Log the extraction
                error_log('Case Study Tags - Extracted special tag: ' . $specialTag);
            }
        }
        
        // Trim and clean up the remaining keywords
        $remainingKeywords = trim(preg_replace('/\s+/', ' ', $remainingKeywords));
        
        // Split the remaining keywords by space
        if (!empty($remainingKeywords)) {
            $normalTags = explode(' ', $remainingKeywords);
            
            // Log the normal tags
            error_log('Case Study Tags - Split normal tags: ' . print_r($normalTags, true));
        } else {
            $normalTags = [];
        }
        
        // Combine special tags and normal tags
        $allTags = array_merge($extractedTags, $normalTags);
        
        // Add all tags to the payload
        foreach ($allTags as $tag) {
            $tag = trim($tag);
            if (!empty($tag)) {
                $payload['tags'][] = [
                    'name' => $tag,
                    'context' => 'tag'
                ];
            }
        }
        
        // Log the final tags
        error_log('Case Study Tags - Final tag count: ' . count($payload['tags']));
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
        
        error_log('Processing synergy fund tags and synergy goal tags');
        
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
                error_log("Adding synergyFundTag: {$tagInfo['name']} (ID: {$tagInfo['id']}) from column $column");
                
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
                    error_log("Added synergyFundTag: {$tag->getName()} (ID: {$tag->getId()})");
                } else {
                    error_log("Warning: Tag not found for {$tagInfo['name']} (ID: {$tagInfo['id']})");
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
                error_log("Adding synergyGoalTag: {$tagInfo['name']} (ID: {$tagInfo['id']}) from column $column");
                
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
                    error_log("Added synergyGoalTag: {$tag->getName()} (ID: {$tag->getId()})");
                } else {
                    error_log("Warning: Tag not found for {$tagInfo['name']} (ID: {$tagInfo['id']})");
                }
            }
        }
        
        error_log("Processed synergy tags: " . count($payload['synergyFundTags']) . " fund tags, " . 
                 count($payload['synergyGoalTags']) . " goal tags");
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
        error_log("Processing case study file attachments");
        
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
            
            error_log("Processing case study file: $filename, $url");
            
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
                        
                        error_log("Added case study file attachment: " . $fileData['name']);
                    }
                }
            } catch (\Exception $e) {
                error_log("Error processing case study file: " . $e->getMessage());
            }
        }
        
        // Process image attachment (BQ/BR)
        if (!empty($data['BQ']) && !empty($data['BR'])) {
            $filename = $data['BQ'];
            $url = $data['BR'];
            
            error_log("Processing case study image: $filename, $url");
            
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
                            
                            error_log("Added case study image attachment: " . $fileData['name']);
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
                            
                            error_log("Added case study image as regular file: " . $fileData['name']);
                        }
                    }
                }
            } catch (\Exception $e) {
                error_log("Error processing case study image: " . $e->getMessage());
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
                    
                    $error = error_get_last();
                    error_log("File download attempt $attempts failed: " . ($error['message'] ?? 'Unknown error'));
                    
                    // Wait before retrying
                    if ($attempts < $maxAttempts) {
                        sleep(1);
                    }
                } catch (\Exception $e) {
                    error_log("Exception in file download attempt $attempts: " . $e->getMessage());
                    
                    // Wait before retrying
                    if ($attempts < $maxAttempts) {
                        sleep(1);
                    }
                }
            }
            
            if ($fileContents === false || $fileContents === null) {
                $error = error_get_last();
                error_log("File download failed after $maxAttempts attempts: " . ($error['message'] ?? 'Unknown error'));
                return null;
            }
            
            // Check if we got an empty response
            if (empty($fileContents)) {
                error_log("File download returned empty content: $url");
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
            error_log("Exception in downloadAttachmentFromUrl: " . $e->getMessage());
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
            error_log('Error in CaseStudyProjectImporter::processImportItem: ' . $e->getMessage());
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
            
            // Log the headers
            error_log('Excel Column Headers for Case Study Import: ' . print_r($headers, true));
            
            // Specifically log the columns we're interested in (BV-CI)
            $interestingColumns = array_filter($headers, function($key) {
                return in_array($key, ['BV', 'BW', 'BX', 'BY', 'BZ', 'CA', 'CB', 'CC', 'CD', 'CE', 'CF', 'CG', 'CH', 'CI']);
            }, ARRAY_FILTER_USE_KEY);
            
            error_log('Case Study Specific Column Headers: ' . print_r($interestingColumns, true));
            
        } catch (\Exception $e) {
            error_log('Error logging Excel headers: ' . $e->getMessage());
        }
    }
} 