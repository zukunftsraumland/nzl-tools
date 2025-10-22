<?php

namespace App\Service\Importer;

use App\Entity\ProjectImport;
use App\Entity\ProjectImportItem;
use App\Entity\User;
use App\Entity\LEPeriod;
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
 *
 * This class inherits the importProjects method from StandardProjectImporter, which supports
 * selectively importing rows based on the selectedRows parameter.
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
    protected function prepareProjectPayload(array $data, array $headerMapping = []): array
    {
        // First, call the parent's method, but we'll override the file processing later
        $payload = parent::prepareProjectPayload($data);
        
        // Set the caseStudy flag to true
        $payload['caseStudy'] = true;
        
        // Map case study specific fields using field codes only
        // Fixed field mapping based on Excel analysis - Q28=Results Quantity, Q29=Innovation, Q30=Mehrwert durch Vernetzung
        $caseStudyFields = [
            'exemplary' => $data['Q21'] ?? null,
            'initialContext' => $data['Q22'] ?? null,
            'initialContextGoals' => $data['Q23'] ?? null,
            'fundingMethod' => $data['Q24'] ?? null,
            'fundingMethodStakeholders' => $data['Q25'] ?? null,
            'resultsQuantity' => $data['Q27'] ?? null,          // Fixed: Q28 = Results Quantity
            'resultsQuality' => $data['Q28'] ?? null,
            'innovations' => $data['Q29'] ?? null,              // Fixed: Q29 = Innovation
            'additionalValue' => $data['Q30'] ?? null,          // Fixed: Q30 = Mehrwert durch Vernetzung  
            'integrationYoungCitizen' => $data['Q31'] ?? null,  // Fixed: Q31 = Integration young citizens
            'integrationFemaleCitizen' => $data['Q32'] ?? null, // Fixed: Q32 = Integration female citizens
            'integrationMinorities' => $data['Q33'] ?? null,    // Fixed: Q33 = Integration minorities
            'learningExperience' => $data['Q34'] ?? null,       // Fixed: Q34 = Learning experience
            'transferable' => $data['Q35'] ?? null,             // Fixed: Q35 = Transferable
        ];
        
        // Assign them to the payload
        foreach ($caseStudyFields as $field => $value) {
            if (!empty($value)) {
                $payload[$field] = $value;
            }
        }
        
        // Clear any file/image entries that might have been added by the parent's processFileAttachmentsFromExcel
        // This is necessary because the parent method may have interpreted these columns as file attachments
        $payload['files'] = [];
        $payload['images'] = [];
        
        // Clear any tags processed by the parent (StandardProjectImporter)
        $payload['tags'] = [];
        
        // Process keywords directly from Q4 field (same as preview method for consistency)
        $keywords = $data['Q4'] ?? '';
        if (!empty($keywords)) {
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
        
        // Process synergy fund tags and synergy goal tags
        $this->processSynergyTags($data, $payload);
        
        // Process files and images from both old and new formats
        $this->processCaseStudyFileAttachments($data, $payload);
        
        // Extract localWorkgroupId from Q7 field (LAG) and map to name - use field codes only
        if (isset($data['Q7']) && is_numeric($data['Q7'])) {
            $localWorkgroupId = (int)$data['Q7'];
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
        
        // Add LE category ID and name - use field codes only
        if (!empty($data['Q6']) && is_numeric($data['Q6'])) {
            $excelCategoryId = (int)$data['Q6'];
            $payload['leFundingCategoryId'] = $excelCategoryId;
            
            // Get the category name from mapping
            $leCategoryNameMapping = $this->getLeCategoryNameMapping();
            if (isset($leCategoryNameMapping[$excelCategoryId])) {
                $payload['leFundingCategoryName'] = $leCategoryNameMapping[$excelCategoryId];
            }
        }
        
        // Override financing processing to use Q10.x fields instead of columns AK, AL, AM
        $this->processCaseStudyFinancing($data, $payload);
        
        // Process links using Q13.1.1/Q13.1.2 fields for case studies
        $this->processCaseStudyLinks($data, $payload, $headerMapping);
        
        // Process videos using Q14.1.1/Q14.1.2 fields for case studies
        $this->processCaseStudyVideos($data, $payload, $headerMapping);
        
        return $payload;
    }
    
   
    /**
     * Process synergy fund tags and synergy goal tags from Excel data
     * 
     * Processes synergy tags using field codes instead of column letters
     * to support both old and new template formats.
     *
     * @param array $data The Excel data
     * @param array &$payload The project payload to update
     */
    private function processSynergyTags(array $data, array &$payload): void
    {
        // Initialize arrays in the payload
        $payload['synergyFundTags'] = [];
        $payload['synergyGoalTags'] = [];
        
        // Process synergyFundTags using correct field codes
        // Q36 is a boolean question, actual fund tags are Q37.1-Q37.5
        $synergyFundTagMappings = [
            'Q37.1' => ['id' => 37, 'name' => 'Europäischer Sozialfonds ESF+'],
            'Q37.2' => ['id' => 38, 'name' => 'Europäischer Fonds für Regionalentwicklung IBW/EFRE'],
            'Q37.3' => ['id' => 39, 'name' => 'INTERREG'],
            'Q37.4' => ['id' => 40, 'name' => 'Europäischer Meeres-, Fischerei- und Aquakulturfonds EMFAF'],
            'Q37.5' => ['id' => 41, 'name' => 'Fonds für einen gerechten Übergang JTF'],
        ];
        
        foreach ($synergyFundTagMappings as $code => $tagInfo) {
            // Check if the field code exists and has a value of 1
            if (isset($data[$code]) && $data[$code] == 1) {
                
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
        
        // Process synergyGoalTags using correct field codes  
        // Q38 is a boolean question, actual goal tags are Q39.1-Q39.7
        $synergyGoalTagMappings = [
            'Q39.1' => ['id' => 43, 'name' => 'Langzeitvision für ländliche Gebiete in Europa bis 2040 (EU Long Term Vision)'],
            'Q39.2' => ['id' => 44, 'name' => 'EU Biodiversitätsstrategie 2030'],
            'Q39.3' => ['id' => 45, 'name' => 'Farm to Fork Strategie'],
            'Q39.4' => ['id' => 46, 'name' => 'EU Digitalisierungsstrategie'],
            'Q39.5' => ['id' => 47, 'name' => 'EU SME Strategie'],
            'Q39.6' => ['id' => 48, 'name' => 'EU Gender Equality Strategie'],
            'Q39.7' => ['id' => 49, 'name' => 'UN-Nachhaltigkeitsziele SDG'],
        ];
        
        foreach ($synergyGoalTagMappings as $code => $tagInfo) {
            // Check if the field code exists and has a value of 1
            if (isset($data[$code]) && $data[$code] == 1) {
                
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
     * Process financing data using Q10.x fields instead of column letters
     * 
     * This method overrides the column-based financing approach from StandardProjectImporter
     * to use Q-based field codes for case studies.
     *
     * @param array $data The Excel data
     * @param array &$payload The project payload to update
     */
    private function processCaseStudyFinancing(array $data, array &$payload): void
    {
        // Initialize standard financing structure with expected IDs
        $payload['financing'] = [
            ['id' => 'costsGap', 'value' => 0],     // GAP Strategieplan
            ['id' => 'costsPrivate', 'value' => 0], // Private und Eigenmittel
            ['id' => 'costsExternal', 'value' => 0] // Andere Finanzquellen
        ];
        
        // Process financing from Q10.x fields (percentages)
        // Q10.1 = GAP Strategieplan percentage
        if (isset($data['Q10.1'])) {
            $value = $data['Q10.1'];
            if (is_string($value)) {
                $value = str_replace(',', '.', $value);
            }
            $value = (float)$value;
            
            if ($value >= 0 && $value <= 100) {
                $payload['financing'][0]['value'] = $value;
            }
        }
        
        // Q10.2 = Private und Eigenmittel percentage
        if (isset($data['Q10.2'])) {
            $value = $data['Q10.2'];
            if (is_string($value)) {
                $value = str_replace(',', '.', $value);
            }
            $value = (float)$value;
            
            if ($value >= 0 && $value <= 100) {
                $payload['financing'][1]['value'] = $value;
            }
        }
        
        // Q10.3 = Andere Finanzquellen percentage
        if (isset($data['Q10.3'])) {
            $value = $data['Q10.3'];
            if (is_string($value)) {
                $value = str_replace(',', '.', $value);
            }
            $value = (float)$value;
            
            if ($value >= 0 && $value <= 100) {
                $payload['financing'][2]['value'] = $value;
            }
        }
        
        // Ensure the values are valid numbers
        foreach ($payload['financing'] as $key => $item) {
            if (!is_numeric($item['value'])) {
                $payload['financing'][$key]['value'] = 0;
            }
        }
    }
    
    /**
     * Process links using fixed column mappings for case studies
     * 
     * Uses fixed column mappings instead of dynamic detection to ensure consistent behavior.
     * Case study projects use format1 columns: BT/BU, BV/BW, BX/BY, BZ/CA, CB/CC
     *
     * @param array $data The Excel data
     * @param array &$payload The project payload to update
     */
    private function processCaseStudyLinks(array $data, array &$payload, array $headerMapping = []): void
    {
        // Initialize links array
        $payload['links'] = [];
           
        // Detect which format has actual data
        $selectedFormat =  [
            'labels' => [],
            'urls' => [],
        ];

        foreach($headerMapping as $column => $identifier) {

            if($identifier === 'Q13.1.1') {
                $selectedFormat['labels'][] = $column;
            }

            if($identifier === 'Q13.1.2') {
                $selectedFormat['urls'][] = $column;
            }

        }
        
        // Use the detected format to process links
        if ($selectedFormat) {
            $maxPairs = min(count($selectedFormat['labels']), count($selectedFormat['urls']));
            for ($i = 0; $i < $maxPairs; $i++) {
                $labelCol = $selectedFormat['labels'][$i];
                $urlCol = $selectedFormat['urls'][$i];
                
                $labelValue = $data[$labelCol] ?? '';
                $urlValue = $data[$urlCol] ?? '';
                
                if (!empty($labelValue) || !empty($urlValue)) {
                    $label = !empty($labelValue) ? trim($labelValue) : '';
                    $url = !empty($urlValue) ? trim($urlValue) : '';
                    
                    // If we have a URL in the label field and no URL in the URL field,
                    // treat the label as a URL
                    if (!empty($label) && empty($url) && $this->looksLikeUrl($label)) {
                        $url = $label;
                        $label = '';
                    }
                    
                    // Skip if no URL is available
                    if (empty($url)) {
                        continue;
                    }
                    
                    // Ensure URL has a protocol
                    if (!preg_match('~^(?:f|ht)tps?://~i', $url)) {
                        $url = 'https://' . $url;
                    }
                    
                    $payload['links'][] = [
                        'url' => $url,
                        'label' => $label,
                        'value' => $url
                    ];
                }
            }
        }
    }
    

    
    /**
     * Process videos using fixed column mappings for case studies
     * 
     * Uses fixed column mappings instead of dynamic detection to ensure consistent behavior.
     * Case study projects use format1 columns: CD/CE, CF/CG, CH/CI
     *
     * @param array $data The Excel data
     * @param array &$payload The project payload to update
     */
    private function processCaseStudyVideos(array $data, array &$payload, array $headerMapping = []): void
    {
        // Initialize videos array
        $payload['videos'] = [];
        
        // Detect which format has actual data
        $selectedFormat =  [
            'labels' => [],
            'urls' => [],
        ];

        foreach($headerMapping as $column => $identifier) {

            if($identifier === 'Q14.1.1') {
                $selectedFormat['labels'][] = $column;
            }

            if($identifier === 'Q14.1.2') {
                $selectedFormat['urls'][] = $column;
            }

        }
        
        // Use the detected format to process videos
        if ($selectedFormat) {
            $maxPairs = min(count($selectedFormat['labels']), count($selectedFormat['urls']));
            for ($i = 0; $i < $maxPairs; $i++) {
                $labelCol = $selectedFormat['labels'][$i];
                $urlCol = $selectedFormat['urls'][$i];
                
                $labelValue = $data[$labelCol] ?? '';
                $urlValue = $data[$urlCol] ?? '';
                
                if (!empty($labelValue) || !empty($urlValue)) {
                    $label = !empty($labelValue) ? trim($labelValue) : '';
                    $url = !empty($urlValue) ? trim($urlValue) : '';
                    
                    // If we have a URL in the label field and no URL in the URL field,
                    // treat the label as a URL
                    if (!empty($label) && empty($url) && $this->looksLikeUrl($label)) {
                        $url = $label;
                        $label = '';
                    }
                    
                    // Skip if no URL is available
                    if (empty($url)) {
                        continue;
                    }
                    
                    // Ensure URL has a protocol
                    if (!preg_match('~^(?:f|ht)tps?://~i', $url)) {
                        $url = 'https://' . $url;
                    }
                    
                    $payload['videos'][] = [
                        'url' => $url,
                        'label' => $label,
                        'value' => $url
                    ];
                }
            }
        }
    }
    
    /**
     * Process links for preview using fixed column mappings for case studies
     * 
     * Uses the same hardcoded format1 columns as the import process to ensure
     * consistency between preview and actual import results.
     *
     * @param array $data The Excel data
     * @param array &$payload The project payload to update
     */
    private function processCaseStudyLinksForPreview(array $data, array &$payload, array $headerMapping = []): void
    {
        // Use the same method as the actual import to ensure consistency
        $this->processCaseStudyLinks($data, $payload, $headerMapping);
    }
    
    /**
     * Process videos for preview using fixed column mappings for case studies
     * 
     * Uses the same hardcoded format1 columns as the import process to ensure
     * consistency between preview and actual import results.
     *
     * @param array $data The Excel data
     * @param array &$payload The project payload to update
     */
    private function processCaseStudyVideosForPreview(array $data, array &$payload, array $headerMapping = []): void
    {
        // Use the same method as the actual import to ensure consistency
        $this->processCaseStudyVideos($data, $payload, $headerMapping);
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
     * Handles file attachments for case study imports from:
     * - Legacy format: BO (file label) and BP (file URL), BQ (image label) and BR (image URL)
     * - New format: Q15.{1-6}.L/N for files, Q16.{1-6}.L/N for images
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
      
               
        // Process new format files (Q15.{1-6}.N/L) - this supports up to 6 files
        $this->importCaseStudyFiles($data, $payload, $existingFileIds);
        
        // Process new format images (Q16.{1-6}.N/L) - this supports up to 6 images with copyright
        $this->importCaseStudyImages($data, $payload, $existingImageIds);
    }
    
    /**
     * Import files from Q15.{1-6}.N/L fields
     * 
     * New format supports up to 6 files where:
     * - Q15.X.N contains the filename/description  
     * - Q15.X.L contains the URL
     * 
     * @param array $data The Excel data
     * @param array &$payload The project payload to update
     * @param array &$existingFileIds Array of existing file IDs to prevent duplicates
     */
    private function importCaseStudyFiles(array $data, array &$payload, array &$existingFileIds): void
    {
        for ($i = 1; $i <= 6; $i++) {
            $nameKey = "Q15.$i.N";  // Filename/description
            $urlKey = "Q15.$i.L";   // URL
            
            if (empty($data[$urlKey])) {
                continue;
            }
            
            $url = $data[$urlKey];
            $filename = $data[$nameKey] ?? basename($url);
            
            try {
                $fileData = $this->downloadAttachmentFromUrl($url, $filename);
                
                if ($fileData && !in_array($fileData['id'], $existingFileIds)) {
                    $payload['files'][] = [
                        'id' => $fileData['id'],
                        'name' => $fileData['name'],
                        'extension' => $fileData['extension'],
                        'mimeType' => $fileData['mimeType'],
                        'description' => $filename,
                    ];
                    $existingFileIds[] = $fileData['id'];
                }
            } catch (\Exception $e) {
                // Log error but continue processing
            }
        }
    }
    
    /**
     * Import images from Q16.{1-6}.N/L fields with copyright from Q17.{1-4}
     * 
     * New format supports up to 6 images where:
     * - Q16.X.N contains the image filename/label
     * - Q16.X.L contains the image URL
     * - Q17.X contains the copyright text for image X (only available for images 1-4)
     * 
     * Note: Only 4 copyright fields exist (Q17.1-Q17.4), so images 5-6 won't have copyright info.
     * 
     * @param array $data The Excel data
     * @param array &$payload The project payload to update
     * @param array &$existingImageIds Array of existing image IDs to prevent duplicates
     */
    private function importCaseStudyImages(array $data, array &$payload, array &$existingImageIds): void
    {
        for ($i = 1; $i <= 6; $i++) {
            $nameKey = "Q16.$i.N";       // Image filename/label
            $urlKey = "Q16.$i.L";        // Image URL
            
            if (empty($data[$urlKey])) {
                continue;
            }
            
            $url = $data[$urlKey];
            $filename = $data[$nameKey] ?? basename($url);
            
            // Copyright is only available for images 1-4 (Q17.1-Q17.4)
            $copyright = '';
            if ($i <= 4) {
                $copyrightKey = "Q17.$i";
                $copyright = $data[$copyrightKey] ?? '';
            }
            
            try {
                $fileData = $this->downloadAttachmentFromUrl($url, $filename);
                
                if ($fileData && !in_array($fileData['id'], $existingImageIds)) {
                    // Check if it's actually an image file
                    if ($this->isImageFile($filename)) {
                        $payload['images'][] = [
                            'id' => $fileData['id'],
                            'name' => $fileData['name'],
                            'extension' => $fileData['extension'],
                            'mimeType' => $fileData['mimeType'],
                            'copyright' => $copyright,
                            'description' => $filename,
                        ];
                        $existingImageIds[] = $fileData['id'];
                    } else {
                        // If it's not an image, treat it as a regular file
                        $payload['files'][] = [
                            'id' => $fileData['id'],
                            'name' => $fileData['name'],
                            'extension' => $fileData['extension'],
                            'mimeType' => $fileData['mimeType'],
                            'description' => $filename,
                        ];
                    }
                }
            } catch (\Exception $e) {
                // Log error but continue processing
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
                $payload = $this->preparePreviewPayload($rowData, $headerMapping);
                
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
    private function preparePreviewPayload(array $data, array $headerMapping = []): array
    {
        $payload = [];
        
        // Basic project fields - use field codes only, no hard-coded column fallbacks
        $payload['title'] = $data['title'] ?? $data['Q2.1'] ?? '';
        $payload['description'] = $data['description'] ?? $data['Q11'] ?? '';
        $payload['projectCode'] = $data['projectCode'] ?? $data['Q2.2'] ?? '';
        
        // Parse dates - use field codes only
        if (!empty($data['Q2.3'])) {
            $startDateValue = $data['Q2.3'];
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
        
        if (!empty($data['Q2.4'])) {
            $endDateValue = $data['Q2.4'];
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
        // Map case study specific fields using field codes only
        // Fixed field mapping to match main method - Q28=Results Quantity, Q29=Innovation, Q30=Mehrwert durch Vernetzung
        $caseStudyFields = [
            'exemplary' => $data['Q21'] ?? null,
            'initialContext' => $data['Q22'] ?? null,
            'initialContextGoals' => $data['Q23'] ?? null,
            'fundingMethod' => $data['Q24'] ?? null,
            'fundingMethodStakeholders' => $data['Q25'] ?? null,
            'resultsQuantity' => $data['Q27'] ?? null,          // Fixed: Q28 = Results Quantity
            'resultsQuality' => $data['Q28'] ?? null,
            'innovations' => $data['Q29'] ?? null,              // Fixed: Q29 = Innovation
            'additionalValue' => $data['Q30'] ?? null,          // Fixed: Q30 = Mehrwert durch Vernetzung  
            'integrationYoungCitizen' => $data['Q31'] ?? null,  // Fixed: Q31 = Integration young citizens
            'integrationFemaleCitizen' => $data['Q32'] ?? null, // Fixed: Q32 = Integration female citizens
            'integrationMinorities' => $data['Q33'] ?? null,    // Fixed: Q33 = Integration minorities
            'learningExperience' => $data['Q34'] ?? null,       // Fixed: Q34 = Learning experience
            'transferable' => $data['Q35'] ?? null,             // Fixed: Q35 = Transferable
        ];
        
        foreach ($caseStudyFields as $field => $value) {
            if (!empty($value)) {
                $payload[$field] = $value;
            }
        }
        
        // For preview only, include placeholder tags without DB lookups - use field codes only
        $keywords = $data['Q4'] ?? '';
        if (!empty($keywords)) {
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
        
        
        // Extract localWorkgroupId from Q7 field (LAG) and map to name - use field codes only
        if (isset($data['Q7']) && is_numeric($data['Q7'])) {
            $localWorkgroupId = (int)$data['Q7'];
            $payload['localWorkgroupId'] = $localWorkgroupId;
            
            // Get the LocalWorkgroup name from mapping
            $localWorkgroupNameMapping = $this->getLocalWorkgroupNameMapping();
            if (isset($localWorkgroupNameMapping[$localWorkgroupId])) {
                $payload['localWorkgroupName'] = $localWorkgroupNameMapping[$localWorkgroupId];
            }
        }
        
        // Add LE category ID and name for preview - use field codes only
        if (!empty($data['Q6']) && is_numeric($data['Q6'])) {
            $excelCategoryId = (int)$data['Q6'];
            $payload['leFundingCategoryId'] = $excelCategoryId;
            
            // Get the category name from mapping
            $leCategoryNameMapping = $this->getLeCategoryNameMapping();
            if (isset($leCategoryNameMapping[$excelCategoryId])) {
                $payload['leFundingCategoryName'] = $leCategoryNameMapping[$excelCategoryId];
            }
        }
        
        // Process financing for preview using Q10.x fields - simplified without validation
        $payload['financing'] = [
            ['id' => 'costsGap', 'value' => isset($data['Q10.1']) ? (float)$data['Q10.1'] : 0],     // GAP Strategieplan
            ['id' => 'costsPrivate', 'value' => isset($data['Q10.2']) ? (float)$data['Q10.2'] : 0], // Private und Eigenmittel
            ['id' => 'costsExternal', 'value' => isset($data['Q10.3']) ? (float)$data['Q10.3'] : 0] // Andere Finanzquellen
        ];
        
        // Process links for preview (with format detection)
        $this->processCaseStudyLinksForPreview($data, $payload, $headerMapping);
        
        // Process videos for preview (with format detection)
        $this->processCaseStudyVideosForPreview($data, $payload, $headerMapping);
        
        return $payload;
    }

    /**
     * Get the mapping between Excel LE-Category IDs and database LE-Category IDs
     * This mapping is specific to CaseStudy imports.
     * 
     * @return array Mapping from Excel ID to database ID
     */
    protected function getLeCategoryMapping(): array
    {
        // This mapping was originally in StandardProjectImporter but is specific to Case Studies
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
     * Get the mapping between Excel LE-Category IDs and their names
     * This mapping is specific to CaseStudy imports.
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
     * Override importProjects to fix the column mapping issue
     * 
     * The parent StandardProjectImporter stores incorrect column mappings in rawData.
     * We need to re-read the Excel file directly to get the correct column data.
     */
    public function importProjects(ProjectImport $import, User $user, ?LEPeriod $lePeriod = null, ?array $selectedRows = null): bool
    {
        try {

            // Update import status
            $import->setStatus(ProjectImport::STATUS_PROCESSING);
            $import->setUpdatedAt(new \DateTime());
            $this->em->persist($import);
            $this->em->flush();

            // Re-read the Excel file directly to get correct column mappings
            $filePath = $import->getFilePath();
            if (!file_exists($filePath)) {
                $filePath = $this->uploadDir . '/' . $filePath;
            }

            // Load the spreadsheet fresh
            $spreadsheet = IOFactory::load($filePath);
            $worksheet = $spreadsheet->getActiveSheet();
            
            $headerRowIndex = $this->getHeaderRowCount();
            $highestRow = $worksheet->getHighestRow();
            $highestColumnIndex = Coordinate::columnIndexFromString($worksheet->getHighestColumn());
            
            // Build correct header mapping from row 4 (field names)
            $headerMapping = [];
            for ($col = 1; $col <= $highestColumnIndex; $col++) {
                $columnLetter = Coordinate::stringFromColumnIndex($col);
                $fieldName = $worksheet->getCellByColumnAndRow($col, $headerRowIndex)->getValue();
                if (!empty($fieldName)) {
                    $headerMapping[$columnLetter] = $fieldName;
                }
            }

            // Get all import items
            $items = $import->getItems();
            
            // If no items exist, create them from the Excel file
            if ($items->isEmpty()) {

                
                // Get the highest row and column indexes
                $highestRow = $worksheet->getHighestRow();
                $highestColumnIndex = Coordinate::columnIndexFromString($worksheet->getHighestColumn());
                
                // Build header mapping from row 1 (parent headers) and row 4 (field names)
                $headerMappingForCreation = [];
                $parentHeaders = [];
                
                // Get parent headers from row 1
                for ($col = 1; $col <= $highestColumnIndex; $col++) {
                    $parentHeader = $worksheet->getCellByColumnAndRow($col, 1)->getValue();
                    if (!empty($parentHeader)) {
                        $parentHeaders[$col] = $parentHeader;
                    }
                }
                
                // Get field names from row 4
                for ($col = 1; $col <= $highestColumnIndex; $col++) {
                    $fieldName = $worksheet->getCellByColumnAndRow($col, 4)->getValue();
                    if (!empty($fieldName)) {
                        // Find the parent header for this column
                        $currentParent = null;
                        foreach ($parentHeaders as $parentCol => $parentValue) {
                            if ($parentCol <= $col) {
                                $currentParent = $parentValue;
                            }
                        }
                        
                        // Store the mapping
                        $headerMappingForCreation[$col] = [
                            'parent' => $currentParent,
                            'field' => $fieldName
                        ];
                    }
                }
                
                // Process each row starting from row 5 (after headers)
                for ($row = 5; $row <= $highestRow; $row++) {
                    $rowData = [];
                    
                    // Process each column
                    for ($col = 1; $col <= $highestColumnIndex; $col++) {
                        if (isset($headerMappingForCreation[$col])) {
                            $cellValue = $worksheet->getCellByColumnAndRow($col, $row)->getValue();
                            $fieldName = $headerMappingForCreation[$col]['field'];
                            $rowData[$fieldName] = $cellValue;
                        }
                        
                        // Also store the column letter as a key for links and videos
                        $colLetter = Coordinate::stringFromColumnIndex($col);
                        $cellValue = $worksheet->getCellByColumnAndRow($col, $row)->getValue();
                        if ($cellValue !== null) {
                            $rowData[$colLetter] = $cellValue;
                        }
                    }
                    
                    // Skip empty rows
                    if (empty($rowData)) {
                        continue;
                    }
                    
                    // Prepare the project payload for preview
                    $payload = $this->preparePreviewPayload($rowData);
                    
                    // Create a new import item
                    $importItem = new ProjectImportItem();
                    $importItem->setImport($import);
                    $importItem->setRowNumber($row - 4); // Adjust for header rows
                    $importItem->setStatus(ProjectImportItem::STATUS_PENDING);
                    $importItem->setRawData($rowData);
                    $importItem->setProcessedData([
                        'rowNumber' => $row - 4,
                        'title' => $payload['title'] ?? 'Untitled',
                        'description' => $payload['description'] ?? '',
                        'projectCode' => $payload['projectCode'] ?? '',
                        'startDate' => $payload['startDate'] ?? null,
                        'endDate' => $payload['endDate'] ?? null,
                        'status' => 'valid',
                        'message' => '',
                        'payload' => $payload
                    ]);
                    $importItem->setCreatedAt(new \DateTime());
                    $importItem->setUpdatedAt(new \DateTime());
                    
                    $this->em->persist($importItem);
                }
                
                $this->em->flush();
                
                // Refresh the items collection
                $this->em->refresh($import);
                $items = $import->getItems();
                
            }
            
            // Process each item with fresh Excel data
            $successCount = 0;
            $errorCount = 0;
            $processedCount = 0;
            
            foreach ($items as $item) {
                // Skip already processed items
                if ($item->getStatus() !== ProjectImportItem::STATUS_PENDING) {
                    continue;
                }
                
                // Skip items not in the selectedRows array if it's provided
                if ($selectedRows !== null && !in_array($item->getRowNumber(), $selectedRows)) {
                    $item->setStatus(ProjectImportItem::STATUS_SKIPPED);
                    $item->setUpdatedAt(new \DateTime());
                    $this->em->persist($item);
                    continue;
                }
                
                // Update item status
                $item->setStatus(ProjectImportItem::STATUS_PROCESSING);
                $item->setUpdatedAt(new \DateTime());
                $this->em->persist($item);
                $this->em->flush();
                
                // Calculate the actual Excel row number (add header rows)
                $excelRowNumber = $item->getRowNumber() + $headerRowIndex;
                
                // Read fresh data directly from Excel for this row
                $freshRowData = [];
                
                // First, read by field names
                for ($col = 1; $col <= $highestColumnIndex; $col++) {
                    $columnLetter = Coordinate::stringFromColumnIndex($col);
                    if (isset($headerMapping[$columnLetter])) {
                        $cellValue = $worksheet->getCellByColumnAndRow($col, $excelRowNumber)->getValue();
                        $fieldName = $headerMapping[$columnLetter];
                        $freshRowData[$fieldName] = $cellValue;
                    }
                }
                
                // Then, read by column letters for links/videos (this time with correct mapping)
                for ($col = 1; $col <= $highestColumnIndex; $col++) {
                    $columnLetter = Coordinate::stringFromColumnIndex($col);
                    $cellValue = $worksheet->getCellByColumnAndRow($col, $excelRowNumber)->getValue();
                    if ($cellValue !== null) {
                        $freshRowData[$columnLetter] = $cellValue;
                    }
                }
                
                                    // Process with fresh data
                try {
                    $fullPayload = $this->prepareProjectPayload($freshRowData, $headerMapping);
                    
                    // Create the result structure
                    $result = [
                        'rowNumber' => $item->getRowNumber(),
                        'title' => $fullPayload['title'] ?? 'Untitled',
                        'description' => $fullPayload['description'] ?? '',
                        'projectCode' => $fullPayload['projectCode'] ?? '',
                        'startDate' => $fullPayload['startDate'] ?? null,
                        'endDate' => $fullPayload['endDate'] ?? null,
                        'status' => 'valid',
                        'message' => '',
                        'payload' => $fullPayload
                    ];
                    
                    // Validate required fields
                    if (empty($result['title'])) {
                        $result['status'] = 'error';
                        $result['message'] = 'Project title is required';
                    }
                    

                    
                    if ($result['status'] === 'valid') {
                        // Get the LE category and local workgroup mappings
                        $leCategoryMapping = $this->getLeCategoryMapping();
                        $localWorkgroupMapping = $this->getLocalWorkgroupMapping();
                        
                        // If LE Period is provided, add it to the payload
                        if ($lePeriod) {
                            $result['payload']['lePeriod'] = $lePeriod;
                            
                            // If LE Category ID is provided in the payload, find and add the LE Category
                            if (isset($result['payload']['leFundingCategoryId']) && is_numeric($result['payload']['leFundingCategoryId'])) {
                                $excelCategoryId = (int)$result['payload']['leFundingCategoryId'];
                                
                                // Get the database ID for the LE-Category
                                $dbCategoryId = $leCategoryMapping[$excelCategoryId] ?? null;
                                
                                if ($dbCategoryId) {
                                    $leCategory = $this->em->getRepository(\App\Entity\LEFundingCategory::class)->find($dbCategoryId);
                                    
                                    if ($leCategory) {
                                        $result['payload']['leFundingCategory'] = $leCategory;
                                    }
                                }
                            }
                        }
                        
                        // If LocalWorkgroup ID is provided in the payload, find and add the LocalWorkgroup
                        if (isset($result['payload']['localWorkgroupId']) && is_numeric($result['payload']['localWorkgroupId'])) {
                            $excelWorkgroupId = (int)$result['payload']['localWorkgroupId'];
                            
                            // Get the database ID for the LocalWorkgroup
                            $dbWorkgroupId = $localWorkgroupMapping[$excelWorkgroupId] ?? null;
                            
                            if ($dbWorkgroupId) {
                                $localWorkgroup = $this->em->getRepository(\App\Entity\LocalWorkgroup::class)->find($dbWorkgroupId);
                                
                                if ($localWorkgroup) {
                                    $result['payload']['localWorkgroup'] = $localWorkgroup;
                                }
                            }
                        }
                        
                        // Check if a project with this title already exists
                        $existingProject = $this->findProjectByTitle($result['payload']['title']);
                        
                        if ($existingProject) {
                            // Update the project with the new data
                            try {
                                $this->projectService->updateProject($existingProject, $result['payload']);
                                
                                $item->setStatus(ProjectImportItem::STATUS_COMPLETED);
                                $item->setProject($existingProject);
                                $successCount++;
                            } catch (\Exception $updateException) {
                                $item->setStatus(ProjectImportItem::STATUS_FAILED);
                                $item->setErrorMessage('Failed to update project: ' . $updateException->getMessage());
                                $errorCount++;
                            }
                        } else {
                            // Set the user as the creator
                            $result['payload']['user'] = $user;
                            
                            // Create the project
                            try {
                                $project = $this->projectService->createProject($result['payload']);
                                
                                if ($project) {
                                    $item->setStatus(ProjectImportItem::STATUS_COMPLETED);
                                    $item->setProject($project);
                                    $successCount++;
                                } else {
                                    $item->setStatus(ProjectImportItem::STATUS_FAILED);
                                    $item->setErrorMessage('Failed to create project - ProjectService returned null');
                                    $errorCount++;
                                }
                            } catch (\Exception $createException) {
                                $item->setStatus(ProjectImportItem::STATUS_FAILED);
                                $item->setErrorMessage('Failed to create project: ' . $createException->getMessage());
                                $errorCount++;
                            }
                        }
                    } else {
                        $item->setStatus(ProjectImportItem::STATUS_FAILED);
                        $item->setErrorMessage($result['message']);
                        $errorCount++;
                    }
                    
                } catch (\Exception $e) {
                    $item->setStatus(ProjectImportItem::STATUS_FAILED);
                    $item->setErrorMessage('Error: ' . $e->getMessage());
                    $errorCount++;
                }
                
                $item->setUpdatedAt(new \DateTime());
                $this->em->persist($item);
                
                $processedCount++;
                
                // Update import progress
                $import->setProcessedRows($processedCount);
                $import->setSuccessfulRows($successCount);
                $import->setErrorRows($errorCount);
                $import->setUpdatedAt(new \DateTime());
                $this->em->persist($import);
                $this->em->flush();
            }
            
            // Update final import status
            $import->setStatus(ProjectImport::STATUS_COMPLETED);
            $import->setUpdatedAt(new \DateTime());
            $this->em->persist($import);
            $this->em->flush();
            

            
            return true;
            
        } catch (\Exception $e) {
            // Update import status
            $import->setStatus(ProjectImport::STATUS_FAILED);
            $import->setErrorMessage('Error: ' . $e->getMessage());
            $import->setUpdatedAt(new \DateTime());
            $this->em->persist($import);
            $this->em->flush();
            
            return false;
        }
    }

} 
