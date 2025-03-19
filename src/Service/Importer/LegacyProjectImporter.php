<?php

namespace App\Service\Importer;

use App\Entity\ProjectImport;
use App\Entity\User;
use App\Entity\LEPeriod;
use App\Service\ProjectService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\String\Slugger\SluggerInterface;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;

/**
 * Legacy Project Importer
 * 
 * This importer will handle the import of legacy projects with a different Excel structure.
 * This is a placeholder for future implementation.
 */
class LegacyProjectImporter extends AbstractProjectImporter
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
     * {@inheritdoc}
     */
    public function getName(): string
    {
        return 'Legacy';
    }

    /**
     * {@inheritdoc}
     */
    public function getDescription(): string
    {
        return 'Import von alten Projekten mit anderer Excel-Struktur';
    }

    /**
     * {@inheritdoc}
     */
    public function getType(): string
    {
        return 'legacy';
    }

    /**
     * {@inheritdoc}
     */
    protected function getHeaderRowCount(): int
    {
        // Legacy Excel format has headers in row 1
        return 1;
    }

    /**
     * {@inheritdoc}
     */
    public function processImportItem(ProjectImport $import, int $rowIndex): array
    {
        try {
            // Get the Excel file path - use the full file path instead of constructing it
            $filePath = $import->getFilePath();
            
            // Check if file exists and is readable
            if (!file_exists($filePath)) {
                // If not absolute, prepend the upload directory
                $absoluteFilePath = $this->uploadDir . '/' . $filePath;
                if (file_exists($absoluteFilePath)) {
                    $filePath = $absoluteFilePath;
                } else {
                    throw new \Exception('Excel file not found at path: ' . $filePath . ' or ' . $absoluteFilePath);
                }
            }
            
            // Load the Excel file
            $spreadsheet = IOFactory::load($filePath);
            $worksheet = $spreadsheet->getActiveSheet();
            
            // Get the highest column index
            $highestColumn = $worksheet->getHighestColumn();
            $highestColumnIndex = Coordinate::columnIndexFromString($highestColumn);
            
            // Get the headers from the first row
            $headers = [];
            for ($col = 1; $col <= $highestColumnIndex; $col++) {
                $columnLetter = Coordinate::stringFromColumnIndex($col);
                $header = $worksheet->getCellByColumnAndRow($col, 1)->getValue();
                if (!empty($header)) {
                    $headers[$columnLetter] = $header;
                }
            }
            
            // Extract data from the specified row
            $rowData = [];
            for ($col = 1; $col <= $highestColumnIndex; $col++) {
                $columnLetter = Coordinate::stringFromColumnIndex($col);
                if (isset($headers[$columnLetter])) {
                    $header = $headers[$columnLetter];
                    $value = $worksheet->getCellByColumnAndRow($col, $rowIndex)->getValue();
                    $rowData[$header] = $value;
                }
            }
            
            // Prepare the project payload
            $payload = $this->prepareProjectPayload($rowData);
            
            return [
                'status' => 'success',
                'data' => $payload
            ];
        } catch (\Exception $e) {
            
            return [
                'status' => 'error',
                'message' => 'Fehler beim Verarbeiten der Zeile ' . $rowIndex . ': ' . $e->getMessage()
            ];
        }
    }

    /**
     * {@inheritdoc}
     */
    protected function prepareProjectPayload(array $data): array
    {
        // Initialize payload with default values
        $payload = [
            'isPublic' => false,
            'title' => $data['TITLE'] ?? 'Kein Titel',
            'description' => $data['TEXTLONG'] ?? $data['TEXTSHORT'] ?? '',
            'caseStudy' => false,
            'exemplary' => false,
            'cooperationProjectAt' => false,
            'cooperationProjectEu' => false,
            'lat' => null,
            'lng' => null,
            'tags' => [],
            'topics' => [],
            'keywords' => '',
            'geographicRegions' => [],
            'countries' => [],
            'states' => [],
            'localWorkgroups' => [],
            'contacts' => [],
            'links' => [],
            'files' => [],
            'images' => [],
            'videos' => [],
            'programs' => [],             // Required field for ProjectService
            'instruments' => [],          // Required field for ProjectService
            'businessSectors' => [],      // Required field for ProjectService
            'translations' => [],
            'synergyFundTags' => [],      // Added field used in ProjectService
            'synergyGoalTags' => [],      // Added field used in ProjectService
            'projectCode' => '',
            'source' => 'legacy_import',  // Set source explicitly in the payload
            'projectCosts' => 0,          // Default project costs to 0
            'financing' => [              // Initialize financing as an array with default value
                [
                    'value' => '0',
                    'id' => 'legacy_import'
                ]
            ],
            'dates' => [],                // Required by ProjectService
            'initialContextGoals' => '',  // String field for Project entity
            'additionalValueResult' => '', // String field for Project entity
            'initialContext' => '',       // Added per Project entity requirements
            'additionalValue' => '',      // Added per Project entity requirements
            'innovations' => '',          // Added per Project entity requirements
            'integrationYoungCitizen' => '',
            'integrationFemaleCitizen' => '',
            'integrationMinorities' => '',
            'learningExperience' => '',
            'transferable' => '',
            'transferDetails' => '',
            'fundingMethod' => '',
            'fundingMethodStakeholders' => '',
            'resultsQuantity' => '',
            'resultsQuality' => '',
            // Remove the nested leCategory structure as it's not used by ProjectService
        ];

        // Set project dates
        if (!empty($data['START'])) {
            try {
                $startDate = null;
                // Handle different date formats
                if (is_string($data['START'])) {
                    // Check if the value is just a year (e.g., "2019")
                    if (preg_match('/^(\d{4})$/', trim($data['START']), $matches)) {
                        // If it's just a year, create a date for January 1st of that year
                        $year = $matches[1];
                        $startDate = new \DateTime($year . '-01-01');
                        
                    } else {
                        // Normal date string
                        $startDate = new \DateTime($data['START']);
                    }
                } elseif ($data['START'] instanceof \DateTime) {
                    $startDate = $data['START'];
                } elseif (is_numeric($data['START'])) {
                    // Check if it's just a year as a number
                    if ($data['START'] >= 1900 && $data['START'] <= 2100) {
                        $startDate = new \DateTime((int)$data['START'] . '-01-01');
                        
                    } else {
                        // Excel date format
                        $startDate = \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($data['START']);
                    }
                }
                
                if ($startDate) {
                    $payload['startDate'] = $startDate->format('Y-m-d');
                    
                    // Also add to the dates array for ProjectService
                    $payload['dates'][] = [
                        'type' => 'startDate',
                        'date' => $startDate->format('Y-m-d')
                    ];
                }
            } catch (\Exception $e) {
                // Invalid date format, log but continue
                
            }
        } else {
            // If no start date was found, set a default for 'dates' array
            $payload['dates'][] = [
                'type' => 'startDate',
                'date' => ''
            ];
        }

        if (!empty($data['PROJECTED_END']) || !empty($data['END'])) {
            try {
                $endDateValue = $data['PROJECTED_END'] ?? $data['END'];
                $endDate = null;
                
                // Handle different date formats
                if (is_string($endDateValue)) {
                    // Check if the value is just a year (e.g., "2019")
                    if (preg_match('/^(\d{4})$/', trim($endDateValue), $matches)) {
                        // If it's just a year, create a date for December 31st of that year
                        $year = $matches[1];
                        $endDate = new \DateTime($year . '-12-31');
                        
                    } else {
                        // Normal date string
                        $endDate = new \DateTime($endDateValue);
                    }
                } elseif ($endDateValue instanceof \DateTime) {
                    $endDate = $endDateValue;
                } elseif (is_numeric($endDateValue)) {
                    // Check if it's just a year as a number
                    if ($endDateValue >= 1900 && $endDateValue <= 2100) {
                        $endDate = new \DateTime((int)$endDateValue . '-12-31');
                        
                    } else {
                        // Excel date format
                        $endDate = \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($endDateValue);
                    }
                }
                
                if ($endDate) {
                    $payload['endDate'] = $endDate->format('Y-m-d');
                    
                    // Also add to the dates array for ProjectService
                    $payload['dates'][] = [
                        'type' => 'endDate',
                        'date' => $endDate->format('Y-m-d')
                    ];
                }
            } catch (\Exception $e) {
                // Invalid date format, log but continue
                
            }
        } else {
            // If no end date was found, set a default for 'dates' array
            $payload['dates'][] = [
                'type' => 'endDate',
                'date' => ''
            ];
        }

        // Set project costs and funding
        if (!empty($data['COST'])) {
            try {
                $totalProjectCost = 0;
                if (is_numeric($data['COST'])) {
                    // If it's already a number, use it directly
                    $totalProjectCost = (float) $data['COST'];
                } else {
                    // If it's a string with European number formatting like '55.044,60 €' or '84.684 €'
                    // Log the original value for debugging
                    
                    
                    // Remove the € symbol and trim whitespace
                    $cleanValue = trim(str_replace('€', '', $data['COST']));
                    
                    // Remove dots (thousand separators in European format)
                    $cleanValue = str_replace('.', '', $cleanValue);
                    
                    // Replace comma (decimal separator in European format) with dot
                    $cleanValue = str_replace(',', '.', $cleanValue);
                    
                    // Log the cleaned value
                    
                    
                    // Convert to float
                    $totalProjectCost = (float) $cleanValue;
                }

                // Set the project costs
                $payload['projectCosts'] = $totalProjectCost;
                
                // Log the cost processing
                
                
                // Initialize funding percentages
                $gapFundingPercentage = 0;
                $externalFundingPercentage = 100;  // Default to 100% external if no FUNDING provided
                
                // Process FUNDING to calculate percentages
                if (!empty($data['FUNDING'])) {
                    $fundingAmount = 0;
                    if (is_numeric($data['FUNDING'])) {
                        // If it's already a number, use it directly
                        $fundingAmount = (float) $data['FUNDING'];
                    } else {
                        // If it's a string with European number formatting
                        // Log the original value for debugging
                        
                        
                        // Remove the € symbol and trim whitespace
                        $cleanValue = trim(str_replace('€', '', $data['FUNDING']));
                        
                        // Remove dots (thousand separators in European format)
                        $cleanValue = str_replace('.', '', $cleanValue);
                        
                        // Replace comma (decimal separator in European format) with dot
                        $cleanValue = str_replace(',', '.', $cleanValue);
                        
                        // Log the cleaned value
                        
                        
                        // Convert to float
                        $fundingAmount = (float) $cleanValue;
                    }
                    
                    // Log the parsed funding amount
                    
                    
                    // Calculate what percentage of total cost the funding represents
                    if ($totalProjectCost > 0) {
                        $gapFundingPercentage = round(($fundingAmount / $totalProjectCost) * 100, 2);
                        
                        // Ensure we don't exceed 100%
                        $gapFundingPercentage = min($gapFundingPercentage, 100);
                        
                        // Calculate the external funding percentage (remainder)
                        $externalFundingPercentage = round(100 - $gapFundingPercentage, 2);
                    }
                    
                    // Log the funding calculations
                    
                    
                } else {
                    
                }
                
                // Set up the financing array with the calculated percentages
                $payload['financing'] = [
                    [
                        'value' => (string)$gapFundingPercentage,
                        'id' => 'costsGap'          // GAP Strategieplan
                    ],
                    [
                        'value' => '0',             // No private funding from the legacy data
                        'id' => 'costsPrivate'      // Private und Eigenmittel
                    ],
                    [
                        'value' => (string)$externalFundingPercentage,
                        'id' => 'costsExternal'     // Andere Finanzquellen
                    ]
                ];
                
                // Log the financing structure
                
            } catch (\Exception $e) {
                
                // Set default financing structure
                $payload['projectCosts'] = 0;
                $payload['financing'] = [
                    [
                        'value' => '0',
                        'id' => 'costsGap'
                    ],
                    [
                        'value' => '0',
                        'id' => 'costsPrivate'
                    ],
                    [
                        'value' => '100',
                        'id' => 'costsExternal'
                    ]
                ];
            }
        } else {
            // No cost data, set defaults
            $payload['projectCosts'] = 0;
            $payload['financing'] = [
                [
                    'value' => '0',
                    'id' => 'costsGap'
                ],
                [
                    'value' => '0',
                    'id' => 'costsPrivate'
                ],
                [
                    'value' => '100',
                    'id' => 'costsExternal'
                ]
            ];
            
        }

        // Set LE period
        if (!empty($data['PERIOD'])) {

            $period = $data['PERIOD'];
            // Check if period contains '14' or '20' for LE 14-20
            if (strpos($period, '14') !== false || strpos($period, '20') !== false) {
                $periodID = 1;
            }
            // Check if period contains '07' or '13' for LE 07-13  
            elseif (strpos($period, '07') !== false || strpos($period, '13') !== false) {
                $periodID = 4;
            }
            // Find LE Period by name
            $lePeriod = $this->em->getRepository(\App\Entity\LEPeriod::class)
                ->findOneBy(['id' => $periodID]);

            // If period ID not found, try finding by name matching
            if (!$lePeriod) {
                // Try to find period by matching the period name against '14-20' or '07-13'
                $allPeriods = $this->em->getRepository(\App\Entity\LEPeriod::class)->findAll();
                foreach ($allPeriods as $p) {
                    $periodName = strtolower($p->getName());
                    
                    // Check for LE 14-20
                    if ((strpos($period, '14') !== false || strpos($period, '20') !== false) &&
                        (strpos($periodName, '14') !== false || strpos($periodName, '20') !== false)) {
                        $lePeriod = $p;
                        break;
                    }
                    // Check for LE 07-13
                    elseif ((strpos($period, '07') !== false || strpos($period, '13') !== false) &&
                        (strpos($periodName, '07') !== false || strpos($periodName, '13') !== false)) {
                        $lePeriod = $p;
                        break;
                    }
                }
            }
            
            if ($lePeriod) {
                $payload['lePeriod'] = $lePeriod->getId();
                

                if($data['PLAN1'] && ($lePeriod->getId() === 1 || strpos($period, '14') !== false)) {
                    // Process PLAN1 data to extract article and category information
                    $plan1Value = trim($data['PLAN1']);
                    
                    
                    // Extract numeric prefix and article name
                    // This regex matches 1-2 digits at the start followed by space and the rest of the string
                    if (preg_match('/^(\d{1,2})\s+(.+)$/', $plan1Value, $matches)) {
                        $numericPrefix = $matches[1];
                        $articleName = $matches[2];
                        
                        // Ensure the numeric prefix is two digits (add leading zero if needed)
                        if (strlen($numericPrefix) === 1) {
                            $numericPrefix = '0' . $numericPrefix;
                        }
                        
                        
                        
                        // Get all LEFundingArticle entities
                        $allArticles = $this->em->getRepository(\App\Entity\LEFundingArticle::class)->findAll();
                        $matchingArticle = null;
                        
                        // Try to find a matching article by name
                        foreach ($allArticles as $article) {
                            $articleNameDb = $article->getName();
                            
                            // Compare the extracted article name with the database article name
                            // Using a fuzzy match with similarity_text for better results
                            similar_text(strtolower($articleName), strtolower($articleNameDb), $similarity);
                            
                            // If similarity is high enough (80% or more), consider it a match
                            if ($similarity >= 80) {
                                $matchingArticle = $article;
                                
                                break;
                            }
                        }
                        
                        // If a matching article was found
                        if ($matchingArticle) {
                            // Get all LEFundingCategory entities
                            $allCategories = $this->em->getRepository(\App\Entity\LEFundingCategory::class)->findAll();
                            $matchingCategory = null;
                            
                            // Try to find a matching category by numeric prefix
                            // Looking for a category with name like "M19 LEADER" when numeric prefix is "19"
                            foreach ($allCategories as $category) {
                                $categoryName = $category->getName();
                                
                                // Check if the category name contains "M" + numericPrefix
                                if (stripos($categoryName, 'M' . $numericPrefix) !== false) {
                                    $matchingCategory = $category;
                                    
                                    break;
                                }
                            }
                            
                            // If both article and category were found, add them to the payload
                            if ($matchingArticle && $matchingCategory) {
                                $payload['leFundingArticle'] = $matchingArticle->getId();
                                $payload['leFundingCategory'] = $matchingCategory->getId();
                                error_log("Added LEFundingArticle ID: " . $matchingArticle->getId() . 
                                        " and LEFundingCategory ID: " . $matchingCategory->getId() . 
                                        " to payload");
                            } else if ($matchingArticle) {
                                // If only the article was found, try to get its category
                                $articleCategory = $matchingArticle->getCategory();
                                if ($articleCategory) {
                                    $payload['leFundingArticle'] = $matchingArticle->getId();
                                    $payload['leFundingCategory'] = $articleCategory->getId();
                                    error_log("Added LEFundingArticle ID: " . $matchingArticle->getId() . 
                                            " and its parent category ID: " . $articleCategory->getId() . 
                                            " to payload");
                                }
                            }
                        } else {
                            
                        }
                    } else {
                        
                    }
                }
                
                // Process PLAN3 data to extract the funding method
                if (!empty($data['PLAN3'])) {
                    $plan3Value = trim($data['PLAN3']);
                    
                    
                    // Check if we already have the LEFundingArticle
                    if (isset($payload['leFundingArticle'])) {
                        $fundingArticleId = $payload['leFundingArticle'];
                        $fundingArticle = $this->em->getRepository(\App\Entity\LEFundingArticle::class)->find($fundingArticleId);
                        
                        if ($fundingArticle) {
                            // Find or create the LEFundingMethod
                            $matchingMethod = null;
                            
                            // Find all methods in the database
                            $methodRepository = $this->em->getRepository(\App\Entity\LEFundingMethod::class);
                            $existingMethods = $methodRepository->findAll();
                            
                            // First, look for exact match
                            foreach ($existingMethods as $method) {
                                if (strtolower(trim($method->getName())) === strtolower(trim($plan3Value))) {
                                    $matchingMethod = $method;
                                    
                                    break;
                                }
                            }
                            
                            // If no exact match, try similarity matching
                            if (!$matchingMethod) {
                                $bestMatch = null;
                                $bestSimilarity = 0;
                                
                                foreach ($existingMethods as $method) {
                                    // Special handling for method names with numeric prefixes like "1.2.1. a) ..."
                                    $methodName = $method->getName();
                                    $plan3Name = $plan3Value;
                                    
                                    // For comparison, normalize method names by removing numeric prefixes and other non-essential parts
                                    $normalizedMethodName = preg_replace('/^[\d\.\s]+[a-z]\)\s*/', '', $methodName);
                                    $normalizedPlan3Name = preg_replace('/^[\d\.\s]+[a-z]\)\s*/', '', $plan3Name);
                                    
                                    // Check if after normalization they're similar
                                    similar_text(
                                        strtolower(trim($normalizedMethodName)), 
                                        strtolower(trim($normalizedPlan3Name)), 
                                        $similarity
                                    );
                                    
                                    if ($similarity > $bestSimilarity && $similarity >= 95) {
                                        $bestMatch = $method;
                                        $bestSimilarity = $similarity;
                                    }
                                }
                                
                                if ($bestMatch) {
                                    $matchingMethod = $bestMatch;
                                    error_log("Found similar method: " . $matchingMethod->getName() . 
                                            " (similarity: " . $bestSimilarity . "%)");
                                }
                            }
                            
                            // If method still not found, create a new one
                            if (!$matchingMethod) {
                                
                                
                                $newMethod = new \App\Entity\LEFundingMethod();
                                $newMethod->setName($plan3Value);
                                $newMethod->setArticle($fundingArticle);
                                
                                $this->em->persist($newMethod);
                                $this->em->flush();
                                
                                $matchingMethod = $newMethod;
                                
                            }
                            
                            // Add the method ID to the payload
                            if ($matchingMethod) {
                                $payload['leFundingMethod'] = $matchingMethod->getId();
                                
                            }
                        } else {
                            
                        }
                    } else {
                        
                    }
                }
            } else {
                $payload['lePeriodName'] = $data['PERIOD']; // Keep this for reference
                
            }
        }

        // Process contact information
        if (!empty($data['CONTACT']) || !empty($data['LEAD_PARTNER'])) {
            // Parse address if it exists
            $postalCode = '';
            $city = '';
            $street = '';
            
            if (!empty($data['ADDRESS'])) {
                // Parse the address field which can be in various formats
                $addressParts = $this->parseAddress($data['ADDRESS']);
                $postalCode = $addressParts['postalCode'];
                $city = $addressParts['city'];
                $street = $addressParts['street'];
            }
            
            $contact = [
                'name' => $data['CONTACT'] ?? '',
                'firstName' => $data['SOURCE_FIRSTNAME'] ?? '',
                'lastName' => $data['SOURCE_LASTNAME'] ?? '',
                'role' => $data['FUNCTION'] ?? '',
                'zipCode' => $postalCode,
                'city' => $city,
                'street' => $street,
                'phone' => $data['TEL'] ?? '',
                'email' => $data['EMAIL'] ?? '',
                'website' => $data['URL'] ?? '',
            ];
            
            $payload['contacts'][] = $contact;
        }

        // Process links
        if (!empty($data['URL'])) {
            $payload['links'][] = [
                'url' => $data['URL'],
                'title' => 'Website',
            ];
        }

        // Process additional text fields
        if (!empty($data['INITIALPOSITION'])) {
            $payload['initialContext'] = $data['INITIALPOSITION'];
        }

        if (!empty($data['TARGET'])) {
            $payload['initialContextGoals'] = (string)$data['TARGET']; // Ensure it's a string
        }

        if (!empty($data['IMPLEMENTATION'])) {
            $payload['fundingMethod'] = $data['IMPLEMENTATION'];
        }

        if (!empty($data['RESULT'])) {
            $payload['resultsQuantity'] = (string)$data['RESULT']; // Ensure it's a string
        }

        if (!empty($data['EXPERIENCE'])) {
            $payload['learningExperience'] = $data['EXPERIENCE'];
        }

        // Process geographic regions
        $regions = ['Burgenland', 'Kärnten', 'Niederösterreich', 'Oberösterreich', 
                   'Salzburg', 'Steiermark', 'Tirol', 'Vorarlberg', 'Wien'];
        
        foreach ($regions as $region) {
            if (isset($data[$region]) && $data[$region] === 'ja') {
                // Look up the GeographicRegion entity by name
                $regionEntity = $this->em->getRepository(\App\Entity\GeographicRegion::class)
                    ->findOneBy(['name' => $region]);
                
                if ($regionEntity) {
                    // Add the entity ID to the payload
                    $payload['geographicRegions'][] = [
                        'id' => $regionEntity->getId(),
                        'name' => $region
                    ];
                    
                } else {
                    // Log that the region wasn't found
                    
                    // Still add the name, but without an ID
                    $payload['geographicRegions'][] = ['name' => $region];
                }
            }
        }

        // Process states (Bundesländer)
        $states = ['Burgenland', 'Kärnten', 'Niederösterreich', 'Oberösterreich', 
                   'Salzburg', 'Steiermark', 'Tirol', 'Vorarlberg', 'Wien'];
        
        foreach ($states as $state) {
            if (isset($data[$state]) && $data[$state] === 'ja') {
                // Look up the State entity by name
                $stateEntity = $this->em->getRepository(\App\Entity\State::class)
                    ->findOneBy(['name' => $state]);
                
                if ($stateEntity) {
                    // Add the entity ID to the payload
                    $payload['states'][] = [
                        'id' => $stateEntity->getId(),
                        'name' => $state
                    ];
                    
                } else {
                    // Log that the state wasn't found
                    
                    // Still add the name, but without an ID
                    $payload['states'][] = ['name' => $state];
                }
            }
        }

        // Process LAGs
        $this->processLagsFromLegacyExcel($data, $payload);

        // Process topics/tags
        $this->processTopicsFromLegacyExcel($data, $payload);

        return $payload;
    }

    /**
     * Process LAGs from legacy Excel data
     */
    private function processLagsFromLegacyExcel(array $data, array &$payload): void
    {
        // Get mapping of LAG columns
        $lagMapping = $this->getLagMapping();
        
        // Collection to store valid LAGs with IDs
        $validLags = [];
        
        foreach ($lagMapping as $excelColumn => $lagName) {
            if (isset($data[$excelColumn]) && $data[$excelColumn] === 'ja') {
                // Look up the LocalWorkgroup entity by name
                $localWorkgroup = $this->em->getRepository(\App\Entity\LocalWorkgroup::class)
                    ->findOneBy(['name' => $lagName]);
                
                if ($localWorkgroup) {
                    // Store the valid LAG with its ID
                    $validLags[] = [
                        'id' => $localWorkgroup->getId(),
                        'name' => $lagName
                    ];
                    
                    
                } else {
                    // Try a more flexible search - perhaps with LIKE instead of exact match
                    $localWorkgroups = $this->em->getRepository(\App\Entity\LocalWorkgroup::class)
                        ->createQueryBuilder('l')
                        ->where('l.name LIKE :name')
                        ->setParameter('name', '%' . $lagName . '%')
                        ->getQuery()
                        ->getResult();
                    
                    if (count($localWorkgroups) > 0) {
                        // Use the first match
                        $localWorkgroup = $localWorkgroups[0];
                        $validLags[] = [
                            'id' => $localWorkgroup->getId(),
                            'name' => $localWorkgroup->getName() // Use the actual name from DB
                        ];
                        $payload['cooperationProjectAt'] = true;
                        
                        
                    } else {
                        
                        // Still add the name, but without an ID
                        $payload['localWorkgroups'][] = ['name' => $lagName];
                    }
                }
            }
        }
        
        // Set the localWorkgroups array in the payload with all valid LAGs
        foreach ($validLags as $lag) {
            $payload['localWorkgroups'][] = $lag;
        }
        
        // Set the primary localWorkgroup to the first valid LAG if any were found
        if (!empty($validLags)) {
            $payload['localWorkgroup'] = $validLags[0]['id'];
            
        } else {
            
        }
    }

    /**
     * Process topics and tags from legacy Excel data
     */
    private function processTopicsFromLegacyExcel(array $data, array &$payload): void
    {
        // Debug logging - get all topics from database for comparison
        $allTopicsInDB = $this->em->getRepository(\App\Entity\Topic::class)->findAll();
        $topicNamesInDB = [];
        foreach ($allTopicsInDB as $dbTopic) {
            $topicNamesInDB[] = $dbTopic->getName();
        }
        
        
        // Get mapping of topic columns
        $topicMapping = $this->getTopicMapping();
        
        
        foreach ($topicMapping as $excelColumn => $topic) {
            if (isset($data[$excelColumn]) && $data[$excelColumn] === 'ja') {
                // First try to find the Topic entity by exact name
                $topicEntity = $this->em->getRepository(\App\Entity\Topic::class)
                    ->findOneBy(['name' => $topic]);
                
                if ($topicEntity) {
                    // Add the entity ID to the payload
                    $payload['topics'][] = [
                        'id' => $topicEntity->getId(),
                        'name' => $topicEntity->getName()
                    ];
                    
                } else {
                    // Try a more flexible search with LIKE query
                    $topicEntities = $this->em->getRepository(\App\Entity\Topic::class)
                        ->createQueryBuilder('t')
                        ->where('t.name LIKE :name')
                        ->setParameter('name', '%' . $topic . '%')
                        ->getQuery()
                        ->getResult();
                    
                    if (count($topicEntities) > 0) {
                        // Use the first match
                        $topicEntity = $topicEntities[0];
                        $payload['topics'][] = [
                            'id' => $topicEntity->getId(),
                            'name' => $topicEntity->getName()
                        ];
                        
                    } else {
                        // Also try normalized name (convert special characters, trim, lowercase)
                        $normalizedName = $this->normalizeString($topic);
                        $allTopics = $this->em->getRepository(\App\Entity\Topic::class)->findAll();
                        $matchFound = false;
                        
                        foreach ($allTopics as $possibleMatch) {
                            $normalizedTopicName = $this->normalizeString($possibleMatch->getName());
                            if ($normalizedName === $normalizedTopicName) {
                                $payload['topics'][] = [
                                    'id' => $possibleMatch->getId(),
                                    'name' => $possibleMatch->getName()
                                ];
                                
                                $matchFound = true;
                                break;
                            }
                        }
                        
                        if (!$matchFound) {
                            // Still couldn't find it, log and add without ID
                            
                            $payload['topics'][] = ['name' => $topic];
                        }
                    }
                }
            }
        }
    }

    /**
     * Normalize a string for case-insensitive comparison
     * 
     * Removes special characters, trims whitespace, and converts to lowercase
     * 
     * @param string $string The string to normalize
     * @return string The normalized string
     */
    private function normalizeString(string $string): string
    {
        // Remove accents and special characters
        $string = transliterator_transliterate('Any-Latin; Latin-ASCII', $string);
        
        // Convert to lowercase and trim
        $string = mb_strtolower(trim($string));
        
        // Remove extra whitespace
        $string = preg_replace('/\s+/', ' ', $string);
        
        return $string;
    }

    /**
     * Get mapping of LAG columns to LAG names
     */
    private function getLagMapping(): array
    {
        return [
            'LAG Almenland & Energieregion Weiz - Gleisdorf' => 'LAG Almenland & Energieregion Weiz - Gleisdorf',
            'LAG Attersee - Attergau (REGATTA)' => 'LAG Attersee - Attergau REGATTA',
            'LAG Biosphäre Lungau' => 'LAG Biosphärenpark Lungau',
            'LAG Bucklige Welt -Wechselland' => 'LAG Bucklige Welt -Wechselland',
            'LAG Donau Niederösterreich-Mitte' => 'LAG Donau Niederösterreich-Mitte',
            'LAG Donau-Böhmerwald' => 'LAG Donau-Böhmerwald',
            'LAG Eferdinger Land' => 'LAG Obst- und Gemüseregion Eferding',
            'LAG Eisenstraße Niederösterreich' => 'LAG Eisenstraße Niederösterreich',
            'LAG Elsbeere Wienerwald' => 'LAG Elsbeere Wienerwald',
            'LAG Ennstal-Ausseerland' => 'LAG Ennstal-Ausseerland',
            'LAG FUMO Regionalentwicklung Fuschlseeregion - Mondseeland' => 'LAG FUMO Regionalentwicklung Fuschlseeregion - Mondseeland',
            'LAG Großglockner Mölltal - Oberdrautal' => 'LAG Großglockner Mölltal - Oberdrautal',
            'LAG Holzwelt Murau' => 'LAG Holzwelt Murau',
            'LAG Hügelland östlich von Graz - Schöcklland' => 'LAG Hügelland östlich von Graz - Schöcklland',
            'LAG InnovationsRegion Murtal' => 'LAG InnovationsRegion Murtal',
            'LAG Kamptal' => 'LAG Kamptal',
            'LAG Kitzbühler Alpen' => 'LAG Kitzbühler Alpen',
            'LAG Kraftspendedörfer Joglland' => 'LAG Kraftspendedörfer Joglland',
            'LAG Kufstein und Umgebung – Untere Schranne – Kaiserwinkel' => 'LAG Kufstein und Umgebung – Untere Schranne – Kaiserwinkel',
            'LAG Kulturerbe Salzkammergut REGIS' => 'LAG REGIS Regionalentwicklung Inneres Salzkammergut',
            'LAG LEADER Mitten im Innviertel' => 'LAG LEADER Mitten im Innviertel',
            'LAG Lebens.Wert.Pongau' => 'LAG Lebenswert Pongau-Tennengau',
            'LAG Liezen Gesäuse' => 'LAG Liezen Gesäuse',
            'LAG Lipizzanerheimat' => 'LAG Lipizzanerheimat',
            'LAG Marchfeld' => 'LAG Marchfeld',
            'LAG Mariazellerland Mürztal' => 'LAG Mariazellerland Mürztal',
            'LAG Mostlandl Hausruck' => 'LAG Mostlandl Hausruck',
            'LAG Mostviertel Mitte' => 'LAG Mostviertel Mitte',
            'LAG Mühlviertler Alm' => 'LAG Mühlviertler Alm',
            'LAG Mühlviertler Kernland' => 'LAG Mühlviertler Kernland',
            'LAG Nationalpark Hohe Tauern' => 'LAG Nationalpark Hohe Tauern',
            'LAG Nationalpark OÖ Kalkalpen' => 'LAG Nationalpark OÖ Kalkalpen',
            'LAG Niederösterreich Süd' => 'LAG Niederösterreich Süd',
            'LAG Nockregion Oberkärnten' => 'LAG Nockregion Oberkärnten',
            'LAG Oberinnviertel-Mattigtal' => 'LAG Zukunft Oberinnviertel-Mattigtal',
            'LAG Oststeirisches Kernland' => 'LAG Oststeirisches Kernland',
            'LAG Perg-Strudengau' => 'LAG Perg-Strudengau',
            'LAG REGIO-V Regionalentwicklung Vorarlberg' => 'LAG REGIO-V Regionalentwicklung Vorarlberg',
            'LAG Region Hermagor' => 'LAG Region Hermagor',
            'LAG Region u.we (Urfahr West)' => 'LAG Region u.we (Urfahr West)',
            'LAG Regionalentwicklung Außerfern - REA' => 'LAG Regionalentwicklung Außerfern - REA',
            'LAG Regionalentwicklung Vöckla-Ager' => 'LAG Regionalentwicklung Vöckla-Ager',
            'LAG Regionalkooperation Unterkärnten' => 'LAG Regionalkooperation Unterkärnten',
            'LAG Regionalmanagement Landeck - RegioL' => 'LAG Regionalmanagement Landeck - RegioL',
            'LAG Regionalmanagement Wipptal' => 'LAG Regionalmanagement Wipptal',
            'LAG Regionalmanagement regio³ Pillerseetal-Leukental-Leogang' => 'LAG Regionalmanagement regio³ Pillerseetal-Leukental-Leogang',
            'LAG Regionalmangement Bezirk Imst' => 'LAG Regionalmangement Bezirk Imst',
            'LAG Regionsmanagement Osttirol' => 'LAG Regionsmanagement Osttirol',
            'LAG Römerland Carnuntum' => 'LAG Römerland Carnuntum',
            'LAG Saalachtal' => 'LAG Saalachtal',
            'LAG Salzburger Seenland' => 'LAG Salzburger Seenland',
            'LAG Sauwald - Pramtal' => 'LAG Sauwald - Pramtal',
            'LAG Schilcherland' => 'LAG Schilcherland',
            'LAG Steirische Eisenstraße' => 'LAG Steirische Eisenstraße',
            'LAG Steirisches Vulkanland' => 'LAG Steirisches Vulkanland',
            'LAG Sterngartl Gusental' => 'LAG Sterngartl Gusental',
            'LAG Südliches Waldviertel - Nibelungengau' => 'LAG Südliches Waldviertel - Nibelungengau',
            'LAG Südsteiermark' => 'LAG Südsteiermark',
            'LAG Thermenland - Wechselland' => 'LAG Thermenland - Wechselland',
            'LAG Tourismusverband Moststraße' => 'LAG Tourismusverband Moststraße',
            'LAG Traunsteinregion' => 'LAG Traunsteinregion',
            'LAG Traunviertler Alpenvorland' => 'LAG Traunviertler Alpenvorland',
            'LAG Triestingtal' => 'LAG Triestingtal',
            'LAG Villach-Umland' => 'LAG Villach-Umland',
            'LAG Vorderland - Walgau - Bludenz' => 'LAG Vorderland - Walgau - Bludenz',
            'LAG Wachau - Dunkelsteinerwald' => 'LAG Wachau - Dunkelsteinerwald',
            'LAG Waldviertler Grenzland' => 'LAG Waldviertler Grenzland',
            'LAG Waldviertler Wohlviertel Region Nationalpark Thayaland' => 'LAG Waldviertler Wohlviertel',
            'LAG Weinviertel - Donauraum' => 'LAG Weinviertel - Donauraum',
            'LAG Weinviertel Manhartsberg' => 'LAG Weinviertel Manhartsberg',
            'LAG Weinviertel Ost' => 'LAG Weinviertel Ost',
            'LAG Wels-LEWEL' => 'LAG Wels-LEWEL',
            'LAG Zukunft Linz-Land' => 'LAG Zukunft Linz-Land',
            'LAG kärnten:mitte' => 'LAG kärnten:mitte',
            'LAG mittelburgenland plus' => 'LAG mittelburgenland plus',
            'LAG nordburgenland plus' => 'LAG nordburgenland plus',
            'LAG südburgenland plus' => 'LAG südburgenland plus'
        ];
    }

    /**
     * Get mapping of topic columns to topic names
     */
    private function getTopicMapping(): array
    {
        return [
            'Basisdienstleistungen, Leader, Gemeinden' => 'Gemeinwohl und Daseinsvorsorge',
            'EIP-AGRI' => 'Bildung, Sensibilisierung und Wissenstransfer',
            'Innovation' => 'Innovation',
            'Klimaschutz und Klimawandel' => 'Klimaschutz',
            'Kulinarik' => 'Lebensmittelverarbeitung und Kulinarik',
            'Land- und Forstwirtschaft inkl. Wertschöpfungskette' => 'nachhaltige Land- und Forstwirtschaft',
            'Umwelt, Biodiversität, Naturschutz' => 'Umwelt und Biodiversität',
            'Klimawandelanpassung' => 'Klimawandelanpassung',
            'Chancengleichheit' => 'Gleichstellung',
            'Bildung & Lebenslanges Lernen' => 'Bildung, Sensibilisierung und Wissenstransfer',
            'Landwirtschaft' => 'nachhaltige Land- und Forstwirtschaft',
            'Luftreinhaltung' => 'Umwelt und Biodiversität',
            'Wertschöpfung' => 'ländliche Wirtschaft/ KMU',
            'LEADER' => 'LEADER',
            'Umweltschutz' => 'Umwelt und Biodiversität',
            'Wissenstransfer' => 'Bildung, Sensibilisierung und Wissenstransfer',
            'Kurze Versorgungsketten' => 'ländliche Wirtschaft/ KMU',
            'Frauen' => 'Gleichstellung',
            'Interkommunale Kooperation' => 'Gemeinwohl und Daseinsvorsorge',
            'Klimaschutz' => 'Klimaschutz',
            'Forstwirtschaft' => 'nachhaltige Land- und Forstwirtschaft',
            'Diversifizierung' => 'ländliche Wirtschaft/ KMU',
            'Erneuerbare Energie' => 'Klimaschutz',
            'Naturschutz' => 'Naturschutz',
            'Wald' => 'nachhaltige Land- und Forstwirtschaft',
            'Lokale Agenda 21' => 'Gemeinwohl und Daseinsvorsorge',
            'Gender' => 'Gleichstellung',
            'Jugend' => 'Jugend',
            'Direktvermarktung' => 'Vermarktung und Vertrieb',
            'Biodiversität' => 'Umwelt und Biodiversität',
            'Gemeindeentwicklung' => 'Gemeinwohl und Daseinsvorsorge',
            'Energieeffizienz' => 'Klimaschutz',
            'Boden' => 'Umwelt und Biodiversität',
            'EIP Europäische Innovationspartnerschaft' => 'Bildung, Sensibilisierung und Wissenstransfer',
            'Standortentwicklung' => 'ländliche Wirtschaft/ KMU',
            'Tierwohl' => 'nachhaltige Land- und Forstwirtschaft',
            'Schutzgebiete' => 'Umwelt und Biodiversität',
            'Tourismus' => 'Tourismus',
            'Kultur' => 'Kultur',
            'Betriebswirtschaft' => 'ländliche Wirtschaft/ KMU',
            'Integration & Soziale Inklusion' => 'Gemeinwohl und Daseinsvorsorge',
            'Alm- & Berglandwirtschaft' => 'nachhaltige Land- und Forstwirtschaft',
            'ÖPUL' => 'Umwelt und Biodiversität',
            'Risikomanagement' => 'nachhaltige Land- und Forstwirtschaft',
            'Leerstand' => 'ländliche Wirtschaft/ KMU',
            'Soziale Dienstleistungen' => 'Gemeinwohl und Daseinsvorsorge',
            'Wasser' => 'Umwelt und Biodiversität',
            'Vermarktung und Vertrieb' => 'Vermarktung und Vertrieb',
            'Mobilität' => 'Mobilität',
            'Gesundheit' => 'Gemeinwohl und Daseinsvorsorge',
            'Landwirtschaftliche Dienstleistungen' => 'ländliche Wirtschaft/ KMU',
            'Lebensmittelverarbeitung' => 'Vermarktung und Vertrieb',
            'KMUs, Gewerbe & Wirtschaft' => 'ländliche Wirtschaft/ KMU',
            'Nahversorgung' => 'Gemeinwohl und Daseinsvorsorge',
            'Gastronomie' => 'Gemeinwohl und Daseinsvorsorge',
            'Gemeinschaftsverpflegung' => 'Gemeinwohl und Daseinsvorsorge',
            'Handel' => 'Vermarktung und Vertrieb',
            'Nachhaltige Landschaftspflege' => 'nachhaltige Land- und Forstwirtschaft'
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function generatePreview(ProjectImport $import): array
    {
        try {
            // Get the Excel file path - use the full file path instead of constructing it
            $filePath = $import->getFilePath();
            
            // Load the Excel file
            $spreadsheet = IOFactory::load($filePath);
            $worksheet = $spreadsheet->getActiveSheet();
            
            // Get the highest row and column
            $highestRow = $worksheet->getHighestRow();
            $highestColumn = $worksheet->getHighestColumn();
            $highestColumnIndex = Coordinate::columnIndexFromString($highestColumn);
            
            // Get the header row count
            $headerRowIndex = $this->getHeaderRowCount();
            
            // Get the headers from the first row
            $headers = [];
            for ($col = 1; $col <= $highestColumnIndex; $col++) {
                $columnLetter = Coordinate::stringFromColumnIndex($col);
                $header = $worksheet->getCellByColumnAndRow($col, $headerRowIndex)->getValue();
                if (!empty($header)) {
                    $headers[$columnLetter] = $header;
                }
            }
            
            // Process the rows to generate preview
            $maxPreviewRows = 100; // Limit the number of rows for preview
            $rowLimit = min($highestRow, $maxPreviewRows + $headerRowIndex);
            
            $preview = [];
            for ($rowIndex = $headerRowIndex + 1; $rowIndex <= $rowLimit; $rowIndex++) {
                // Extract data from the row
                $rowData = [];
                
                // Process each column
                for ($col = 1; $col <= $highestColumnIndex; $col++) {
                    $columnLetter = Coordinate::stringFromColumnIndex($col);
                    if (isset($headers[$columnLetter])) {
                        $header = $headers[$columnLetter];
                        $value = $worksheet->getCellByColumnAndRow($col, $rowIndex)->getValue();
                        $rowData[$header] = $value;
                    }
                }
                
                // Skip empty rows
                if (empty($rowData)) {
                    continue;
                }
                
                // Prepare the project payload for preview
                $payload = $this->preparePreviewPayload($rowData);
                
                // Create a preview item with status and message fields
                $previewItem = [
                    'rowIndex' => $rowIndex,
                    'title' => $payload['title'] ?? 'Kein Titel',
                    'description' => mb_substr($payload['description'] ?? '', 0, 100) . (strlen($payload['description'] ?? '') > 100 ? '...' : ''),
                    'projectCode' => $payload['projectCode'] ?? '',
                    'startDate' => $payload['startDate'] ?? null,
                    'endDate' => $payload['endDate'] ?? null,
                    'status' => 'valid', // Default status
                    'message' => '', // Default message
                    'data' => $payload
                ];
                
                // Check if a project with this title already exists
                if (!empty($payload['title'])) {
                    $existingProject = $this->findProjectByTitle($payload['title']);
                    if ($existingProject) {
                        $previewItem['status'] = 'warning';
                        $previewItem['message'] = 'Projekt mit diesem Namen existiert bereits (ID: ' . $existingProject->getId() . ') und wird aktualisiert';
                        $previewItem['existingProjectId'] = $existingProject->getId();
                    }
                }
                
                $preview[] = $previewItem;
            }
            
            // Update the import with total rows
            $import->setTotalRows($highestRow - $headerRowIndex);
            $this->em->persist($import);
            $this->em->flush();
            
            return $preview;
        } catch (\Exception $e) {
            // Log the exception for debugging
            
            return [];
        }
    }
    
    /**
     * Prepare a simplified payload for preview
     */
    private function preparePreviewPayload(array $data): array
    {
        // Get the full payload
        $fullPayload = $this->prepareProjectPayload($data);
        
        // Return a simplified version for preview
        return [
            'title' => $fullPayload['title'] ?? 'Kein Titel',
            'description' => $fullPayload['description'] ?? '',
            'startDate' => $fullPayload['startDate'] ?? '',
            'endDate' => $fullPayload['endDate'] ?? '',
            'projectCosts' => $fullPayload['projectCosts'] ?? 0,
            'financing' => $fullPayload['financing'] ?? [],
            'lePeriodName' => $fullPayload['lePeriodName'] ?? '',
            'leFundingCategoryName' => $fullPayload['leFundingCategoryName'] ?? '',
            'leFundingArticleName' => $fullPayload['leFundingArticleName'] ?? '',
            'leFundingMethodName' => $fullPayload['leFundingMethodName'] ?? '',
            'topics' => $fullPayload['topics'] ?? [],
            'keywords' => $fullPayload['keywords'] ?? '',
            'geographicRegions' => $fullPayload['geographicRegions'] ?? [],
            'localWorkgroups' => $fullPayload['localWorkgroups'] ?? [],
            'contactCount' => count($fullPayload['contacts'] ?? []),
            'linkCount' => count($fullPayload['links'] ?? []),
            'projectCode' => $fullPayload['projectCode'] ?? '',
            'initialContext' => $fullPayload['initialContext'] ?? '',
            'initialContextGoals' => $fullPayload['initialContextGoals'] ?? '',
            'additionalValue' => $fullPayload['additionalValue'] ?? '',
            'additionalValueResult' => $fullPayload['additionalValueResult'] ?? '',
            'learningExperience' => $fullPayload['learningExperience'] ?? '',
            'dates' => $fullPayload['dates'] ?? [],  // Include dates in preview
            'source' => $fullPayload['source'] ?? 'legacy_import',
            // Additional fields needed for complete preview
            'programs' => $fullPayload['programs'] ?? [],
            'instruments' => $fullPayload['instruments'] ?? [],
            'businessSectors' => $fullPayload['businessSectors'] ?? [],
            'tags' => $fullPayload['tags'] ?? [],
            'countries' => $fullPayload['countries'] ?? [],
            'states' => $fullPayload['states'] ?? [],
            'translations' => $fullPayload['translations'] ?? [],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function importProjects(ProjectImport $import, User $user, ?LEPeriod $lePeriod = null): bool
    {
        try {
            // Set the import status to processing right at the beginning
            $import->setStatus('processing');
            $import->setUpdatedAt(new \DateTime());
            $this->em->persist($import);
            $this->em->flush();
            
            // Get the Excel file path - use the full file path instead of constructing it
            $filePath = $import->getFilePath();
            
            // Check if file exists and is readable
            if (!file_exists($filePath)) {
                // Try with the upload directory prefix
                $absoluteFilePath = $this->uploadDir . '/' . $filePath;
                if (file_exists($absoluteFilePath)) {
                    $filePath = $absoluteFilePath;
                } else {
                    throw new \Exception('Excel file not found at path: ' . $filePath . ' or ' . $absoluteFilePath);
                }
            }
            
            if (!is_readable($filePath)) {
                throw new \Exception('Excel file is not readable at path: ' . $filePath);
            }
            
            // Log file path for debugging
            
            
            // Load the Excel file
            try {
                $spreadsheet = IOFactory::load($filePath);
            } catch (\Exception $e) {
                throw new \Exception('Failed to load Excel file: ' . $e->getMessage());
            }
            
            $worksheet = $spreadsheet->getActiveSheet();
            
            // Get the highest row and column
            $highestRow = $worksheet->getHighestRow();
            $highestColumn = $worksheet->getHighestColumn();
            $highestColumnIndex = Coordinate::columnIndexFromString($highestColumn);
            
            // Log sheet dimensions for debugging
            
            
            // Get the header row count
            $headerRowIndex = $this->getHeaderRowCount();
            
            // Get the headers from the first row
            $headers = [];
            for ($col = 1; $col <= $highestColumnIndex; $col++) {
                $columnLetter = Coordinate::stringFromColumnIndex($col);
                $header = $worksheet->getCellByColumnAndRow($col, $headerRowIndex)->getValue();
                if (!empty($header)) {
                    $headers[$columnLetter] = $header;
                }
            }
            
            // Log headers for debugging
            
            
            // Initialize counters
            $processedRows = 0;
            $successfulRows = 0;
            $errorRows = 0;
            $errors = [];
            
            // Process each row
            for ($rowIndex = $headerRowIndex + 1; $rowIndex <= $highestRow; $rowIndex++) {
                // Extract data from the row
                $rowData = [];
                
                // Process each column
                for ($col = 1; $col <= $highestColumnIndex; $col++) {
                    $columnLetter = Coordinate::stringFromColumnIndex($col);
                    if (isset($headers[$columnLetter])) {
                        $header = $headers[$columnLetter];
                        $value = $worksheet->getCellByColumnAndRow($col, $rowIndex)->getValue();
                        $rowData[$header] = $value;
                    }
                }
                
                // Skip empty rows
                if (empty($rowData['TITLE'])) {
                    continue;
                }
                
                $processedRows++;
                
                
                // Process the row
                try {
                    $result = $this->processImportItem($import, $rowIndex);
                } catch (\Exception $e) {
                    
                    $errorRows++;
                    $errors[] = 'Fehler in Zeile ' . $rowIndex . ': ' . $e->getMessage();
                    continue;
                }
                
                if ($result['status'] === 'success') {
                    // Create the project
                    $projectData = $result['data'];
                    
                    try {
                        // Validate and fix payload structure before creating the project
                        $this->validatePayloadStructure($projectData);

                        // Log the final data structure before creating the project
                        

                        // Check if a project with this title already exists
                        $existingProject = $this->findProjectByTitle($projectData['title']);
                        
                        if ($existingProject) {
                            // Update the existing project instead of creating a new one
                            $project = $this->projectService->updateProject($existingProject, $projectData);
                            
                        } else {
                            // Create a new project using the project service
                            $project = $this->projectService->createProject($projectData);
                            
                        }
                        
                        // Set the LE period if provided
                        if ($lePeriod) {
                            $project->setLePeriod($lePeriod);
                            
                        } elseif (!empty($projectData['lePeriodName'])) {
                            // Try to find the LE period by name
                            $lePeriodRepo = $this->em->getRepository(LEPeriod::class);
                            $foundLePeriod = $lePeriodRepo->findOneBy(['name' => $projectData['lePeriodName']]);
                            if ($foundLePeriod) {
                                $project->setLePeriod($foundLePeriod);
                                
                            } else {
                                
                            }
                        }
                        
                        // Set the source to legacy import
                        $project->setSource('legacy_import');
                        
                        // Save the project
                        $this->em->persist($project);
                        $this->em->flush();
                        
                        $successfulRows++;
                        
                    } catch (\Exception $e) {
                        
                        $errorRows++;
                        $errors[] = 'Fehler in Zeile ' . $rowIndex . ': ' . $e->getMessage();
                    }
                } else {
                    
                    $errorRows++;
                    $errors[] = 'Fehler in Zeile ' . $rowIndex . ': ' . $result['message'];
                }
            }
            
            // Update the import status
            $import->setProcessedRows($processedRows);
            $import->setSuccessfulRows($successfulRows);
            $import->setErrorRows($errorRows);
            
            if ($errorRows > 0) {
                if ($successfulRows > 0) {
                    $import->setStatus('partial');
                    
                } else {
                    $import->setStatus('failed');
                    
                }
                $import->setErrorMessage(implode("\n", $errors));
            } else {
                $import->setStatus('completed');
                
            }
            
            $import->setUpdatedAt(new \DateTime());
            $this->em->persist($import);
            $this->em->flush();
            
            return $errorRows === 0;
        } catch (\Exception $e) {
            
            $import->setStatus('failed');
            $import->setErrorMessage('Fehler beim Import: ' . $e->getMessage());
            $import->setUpdatedAt(new \DateTime());
            $this->em->persist($import);
            $this->em->flush();
            
            return false;
        }
    }

    /**
     * Validates and fixes the payload structure to ensure all fields have the correct types
     */
    private function validatePayloadStructure(array &$payload): void
    {
        // Define expected types for critical fields
        $stringFields = [
            'title', 'description', 'projectCode', 'keywords', 'initialContextGoals', 
            'additionalValueResult', 'initialContext', 'additionalValue', 'innovations',
            'integrationYoungCitizen', 'integrationFemaleCitizen', 'integrationMinorities',
            'learningExperience', 'transferable', 'transferDetails', 'fundingMethod',
            'fundingMethodStakeholders', 'resultsQuantity', 'resultsQuality'
        ];
        
        $arrayFields = [
            'dates', 'financing', 'links', 'videos', 'images', 'files', 'translations', 
            'contacts', 'topics', 'tags', 'geographicRegions', 'countries', 'states', 
            'localWorkgroups', 'programs', 'instruments', 'businessSectors', 
            'synergyFundTags', 'synergyGoalTags'
        ];
        
        $booleanFields = [
            'isPublic', 'caseStudy', 'exemplary', 'cooperationProjectAt', 'cooperationProjectEu'
        ];
        
        // Validate and fix string fields
        foreach ($stringFields as $field) {
            if (isset($payload[$field]) && !is_string($payload[$field])) {
                
                $payload[$field] = (string)$payload[$field];
            } elseif (!isset($payload[$field])) {
                // Initialize missing string fields with empty strings
                $payload[$field] = '';
            }
        }
        
        // Validate and fix array fields
        foreach ($arrayFields as $field) {
            if (!isset($payload[$field])) {
                
                $payload[$field] = [];
            } elseif (!is_array($payload[$field])) {
                
                
                // Special handling for financing field
                if ($field === 'financing' && is_numeric($payload[$field])) {
                    $value = (float)$payload[$field];
                    $payload[$field] = [
                        [
                            'value' => (string)$value,
                            'id' => 'legacy_import'
                        ]
                    ];
                } else {
                    $payload[$field] = [];
                }
            }
        }
        
        // Validate and fix boolean fields
        foreach ($booleanFields as $field) {
            if (isset($payload[$field]) && !is_bool($payload[$field])) {
                
                $payload[$field] = (bool)$payload[$field];
            } elseif (!isset($payload[$field])) {
                // Initialize missing boolean fields as false
                $payload[$field] = false;
            }
        }
        
        // Ensure dates field has required structure with at least startDate and endDate
        $hasStartDate = false;
        $hasEndDate = false;
        
        if (is_array($payload['dates'])) {
            foreach ($payload['dates'] as $date) {
                if (isset($date['type']) && $date['type'] === 'startDate') {
                    $hasStartDate = true;
                }
                if (isset($date['type']) && $date['type'] === 'endDate') {
                    $hasEndDate = true;
                }
            }
        }
        
        if (!$hasStartDate) {
            
            $payload['dates'][] = [
                'type' => 'startDate',
                'date' => isset($payload['startDate']) ? $payload['startDate'] : ''
            ];
        }
        
        if (!$hasEndDate) {
            
            $payload['dates'][] = [
                'type' => 'endDate',
                'date' => isset($payload['endDate']) ? $payload['endDate'] : ''
            ];
        }
        
        // Ensure entity fields have the expected structure with 'name' or 'id' properties
        $entityFields = [
            'localWorkgroups', 'topics', 'tags', 'geographicRegions', 'countries', 'states',
            'programs', 'instruments', 'businessSectors', 'synergyFundTags', 'synergyGoalTags'
        ];
        
        foreach ($entityFields as $field) {
            if (isset($payload[$field]) && is_array($payload[$field])) {
                foreach ($payload[$field] as $key => $item) {
                    // If it's just a string, convert it to the expected format
                    if (is_string($item)) {
                        
                        $payload[$field][$key] = ['name' => $item];
                    } elseif (is_array($item) && !isset($item['name']) && !isset($item['id'])) {
                        // If it's an array but doesn't have 'name' or 'id' properties, remove it
                        
                        unset($payload[$field][$key]);
                    }
                }
                
                // Re-index the array after potential removals
                $payload[$field] = array_values($payload[$field]);
            }
        }
        
        // Special handling for localWorkgroups - ensure they have valid IDs
        if (isset($payload['localWorkgroups']) && is_array($payload['localWorkgroups'])) {
            foreach ($payload['localWorkgroups'] as $key => $item) {
                // If there's no ID but there is a name, try to look up the entity by name
                if ((!isset($item['id']) || empty($item['id'])) && isset($item['name']) && !empty($item['name'])) {
                    $localWorkgroup = $this->em->getRepository(\App\Entity\LocalWorkgroup::class)
                        ->findOneBy(['name' => $item['name']]);
                    
                    if ($localWorkgroup) {
                        $payload['localWorkgroups'][$key]['id'] = $localWorkgroup->getId();
                        
                    } else {
                        // If we can't find the localWorkgroup, it's safer to remove it
                        
                        unset($payload['localWorkgroups'][$key]);
                    }
                }
            }
            
            // Re-index the array after potential removals
            $payload['localWorkgroups'] = array_values($payload['localWorkgroups']);
        }
        
        // Special handling for topics - ensure they have valid IDs
        if (isset($payload['topics']) && is_array($payload['topics'])) {
            foreach ($payload['topics'] as $key => $item) {
                // If there's no ID but there is a name, try to look up the entity by name
                if ((!isset($item['id']) || empty($item['id'])) && isset($item['name']) && !empty($item['name'])) {
                    // First try exact name match
                    $topic = $this->em->getRepository(\App\Entity\Topic::class)
                        ->findOneBy(['name' => $item['name']]);
                    
                    if ($topic) {
                        $payload['topics'][$key]['id'] = $topic->getId();
                        
                    } else {
                        // Try a more flexible search with LIKE query
                        $topicEntities = $this->em->getRepository(\App\Entity\Topic::class)
                            ->createQueryBuilder('t')
                            ->where('t.name LIKE :name')
                            ->setParameter('name', '%' . $item['name'] . '%')
                            ->getQuery()
                            ->getResult();
                        
                        if (count($topicEntities) > 0) {
                            // Use the first match
                            $topic = $topicEntities[0];
                            $payload['topics'][$key]['id'] = $topic->getId();
                            
                        } else {
                            // Also try normalized name (convert special characters, trim, lowercase)
                            $normalizedName = $this->normalizeString($item['name']);
                            $allTopics = $this->em->getRepository(\App\Entity\Topic::class)->findAll();
                            $matchFound = false;
                            
                            foreach ($allTopics as $possibleMatch) {
                                $normalizedTopicName = $this->normalizeString($possibleMatch->getName());
                                if ($normalizedName === $normalizedTopicName) {
                                    $payload['topics'][$key]['id'] = $possibleMatch->getId();
                                    
                                    $matchFound = true;
                                    break;
                                }
                            }
                            
                            if (!$matchFound) {
                                // If we can't find the topic, remove it
                                
                                unset($payload['topics'][$key]);
                            }
                        }
                    }
                }
            }
            
            // Re-index the array after potential removals
            $payload['topics'] = array_values($payload['topics']);
        }
        
        // Special handling for geographicRegions - ensure they have valid IDs
        if (isset($payload['geographicRegions']) && is_array($payload['geographicRegions'])) {
            foreach ($payload['geographicRegions'] as $key => $item) {
                // If there's no ID but there is a name, try to look up the entity by name
                if ((!isset($item['id']) || empty($item['id'])) && isset($item['name']) && !empty($item['name'])) {
                    $region = $this->em->getRepository(\App\Entity\GeographicRegion::class)
                        ->findOneBy(['name' => $item['name']]);
                    
                    if ($region) {
                        $payload['geographicRegions'][$key]['id'] = $region->getId();
                        
                    } else {
                        // If we can't find the region, remove it
                        
                        unset($payload['geographicRegions'][$key]);
                    }
                }
            }
            
            // Re-index the array after potential removals
            $payload['geographicRegions'] = array_values($payload['geographicRegions']);
        }
        
        // Special handling for states - ensure they have valid IDs
        if (isset($payload['states']) && is_array($payload['states'])) {
            foreach ($payload['states'] as $key => $item) {
                // If there's no ID but there is a name, try to look up the entity by name
                if ((!isset($item['id']) || empty($item['id'])) && isset($item['name']) && !empty($item['name'])) {
                    $state = $this->em->getRepository(\App\Entity\State::class)
                        ->findOneBy(['name' => $item['name']]);
                    
                    if ($state) {
                        $payload['states'][$key]['id'] = $state->getId();
                        
                    } else {
                        // If we can't find the state, remove it
                        
                        unset($payload['states'][$key]);
                    }
                }
            }
            
            // Re-index the array after potential removals
            $payload['states'] = array_values($payload['states']);
        }
    }

    /**
     * Parse an address string into components (postal code, city, street)
     * 
     * Handles different address formats:
     * - "Postal code City, Street" (e.g., "9991 Dölsach, Stribach 97")
     * - "Street, Postal code City" (e.g., "Innsbrucker Straße 77, 6380 St. Johann i. T.")
     * 
     * @param string $address The full address string
     * @return array Associative array with postalCode, city, and street
     */
    private function parseAddress(string $address): array
    {
        // Default values
        $result = [
            'postalCode' => '',
            'city' => '',
            'street' => ''
        ];
        
        // Trim the address
        $address = trim($address);
        
        // If empty, return default
        if (empty($address)) {
            return $result;
        }
        
        // Log the original address for debugging
        
        
        // Common Austrian postal code pattern: 4-5 digits
        $postalCodePattern = '/\b\d{4,5}\b/';
        
        // Check if postal code exists in the address
        if (preg_match($postalCodePattern, $address, $matches)) {
            $postalCode = $matches[0];
            
            // Determine the format by checking if postal code is at the beginning
            if (strpos($address, $postalCode) === 0) {
                // Format: "Postal code City, Street"
                // Extract the part after postal code until the comma
                if (preg_match('/^\d{4,5}\s+([^,]+),\s*(.+)$/', $address, $matches)) {
                    $result['postalCode'] = $postalCode;
                    $result['city'] = trim($matches[1]);
                    $result['street'] = trim($matches[2]);
                } else {
                    // If no comma, try to split postal code and city
                    if (preg_match('/^\d{4,5}\s+(.+)$/', $address, $matches)) {
                        $result['postalCode'] = $postalCode;
                        $result['city'] = trim($matches[1]);
                        $result['street'] = '';
                    }
                }
            } else {
                // Format: "Street, Postal code City"
                // Split by comma
                $parts = explode(',', $address);
                
                if (count($parts) >= 2) {
                    $result['street'] = trim($parts[0]);
                    
                    // Extract postal code and city from the last part
                    $lastPart = trim(end($parts));
                    
                    if (preg_match('/(\d{4,5})\s+(.+)/', $lastPart, $matches)) {
                        $result['postalCode'] = $matches[1];
                        $result['city'] = trim($matches[2]);
                    } else {
                        $result['city'] = $lastPart;
                    }
                }
            }
        } else {
            // No postal code found, try to split by comma
            $parts = explode(',', $address);
            
            if (count($parts) >= 2) {
                $result['street'] = trim($parts[0]);
                $result['city'] = trim(end($parts));
            } else {
                // No comma, treat the whole string as street
                $result['street'] = $address;
            }
        }
        
        // Log the parsed result for debugging
        
        
        return $result;
    }
} 