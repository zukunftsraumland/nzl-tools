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
 * Handles the import of legacy projects from Excel files with a different structure than the standard importer.
 * This importer supports various date formats, address formats, and mapping of legacy fields to current entities.
 * 
 * Key features:
 * - Imports projects from legacy Excel format
 * - Maps old field names to new entity structure
 * - Handles various date and address formats
 * - Supports fuzzy matching for LAGs and topics
 * - Validates and normalizes data before import
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
     * 
     */
    public function getName(): string
    {
        return 'Legacy';
    }

    /**
     * 
     */
    public function getDescription(): string
    {
        return 'Import von alten Projekten mit anderer Excel-Struktur';
    }

    /**
     * 
     */
    public function getType(): string
    {
        return 'legacy';
    }

    /**
     * 
     */
    protected function getHeaderRowCount(): int
    {
        // Legacy Excel format has headers in row 1
        return 1;
    }

    /**
     * Process a single row from the Excel file and convert it to a project payload.
     * 
     * This method:
     * 1. Loads and validates the Excel file
     * 2. Extracts headers and row data
     * 3. Converts the row data into a project payload
     * 
     * @param ProjectImport $import The import entity containing file path and metadata
     * @param int $rowIndex The 1-based index of the row to process
     * @return array{status: string, data?: array, message?: string} Success/error status with payload or error message
     * @throws \Exception If file cannot be found or read
     */
    public function processImportItem(ProjectImport $import, int $rowIndex): array
    {
        try {
            $filePath = $import->getFilePath();
            
            if (!file_exists($filePath)) {
                $absoluteFilePath = $this->uploadDir . '/' . $filePath;
                if (file_exists($absoluteFilePath)) {
                    $filePath = $absoluteFilePath;
                } else {
                    throw new \Exception('Excel file not found at path: ' . $filePath . ' or ' . $absoluteFilePath);
                }
            }
            
            $spreadsheet = IOFactory::load($filePath);
            $worksheet = $spreadsheet->getActiveSheet();
            
            $highestColumn = $worksheet->getHighestColumn();
            $highestColumnIndex = Coordinate::columnIndexFromString($highestColumn);
            
            $headers = [];
            for ($col = 1; $col <= $highestColumnIndex; $col++) {
                $columnLetter = Coordinate::stringFromColumnIndex($col);
                $header = $worksheet->getCellByColumnAndRow($col, 1)->getValue();
                if (!empty($header)) {
                    $headers[$columnLetter] = $header;
                }
            }
            
            $rowData = [];
            for ($col = 1; $col <= $highestColumnIndex; $col++) {
                $columnLetter = Coordinate::stringFromColumnIndex($col);
                if (isset($headers[$columnLetter])) {
                    $header = $headers[$columnLetter];
                    $value = $worksheet->getCellByColumnAndRow($col, $rowIndex)->getValue();
                    $rowData[$header] = $value;
                }
            }
            
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
     * Prepare a project payload from legacy Excel row data.
     * 
     * This method transforms raw Excel data into a structured project payload by:
     * - Setting default values for all required fields
     * - Converting legacy field names to current entity structure
     * - Processing dates in various formats (year only, Excel dates, etc.)
     * - Calculating funding percentages from cost data
     * - Mapping legacy LAGs and topics to current entities
     * - Processing addresses and contact information
     * 
     * @param array $data Raw data from Excel row
     * @return array Structured project payload ready for import
     */
    protected function prepareProjectPayload(array $data): array
    {
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
            'programs' => [],
            'instruments' => [],
            'businessSectors' => [],
            'translations' => [],
            'synergyFundTags' => [],
            'synergyGoalTags' => [],
            'projectCode' => '',
            'source' => 'legacy_import',
            'projectCosts' => 0,
            'financing' => [
                [
                    'value' => '0',
                    'id' => 'legacy_import'
                ]
            ],
            'dates' => [],
            'initialContextGoals' => '',
            'additionalValueResult' => '',
            'initialContext' => '',
            'additionalValue' => '',
            'innovations' => '',
            'integrationYoungCitizen' => '',
            'integrationFemaleCitizen' => '',
            'integrationMinorities' => '',
            'learningExperience' => '',
            'transferable' => '',
            'transferDetails' => '',
            'fundingMethod' => '',
            'fundingMethodStakeholders' => '',
            'resultsQuantity' => '',
            'resultsQuality' => ''
        ];

        if (!empty($data['START'])) {
            try {
                $startDate = $this->parseLegacyDate($data['START']);
                if ($startDate) {
                    $payload['startDate'] = $startDate->format('Y-m-d');
                    $payload['dates'][] = [
                        'type' => 'startDate',
                        'date' => $startDate->format('Y-m-d')
                    ];
                }
            } catch (\Exception $e) {
                $payload['dates'][] = [
                    'type' => 'startDate',
                    'date' => ''
                ];
            }
        } else {
            $payload['dates'][] = [
                'type' => 'startDate',
                'date' => ''
            ];
        }

        if (!empty($data['PROJECTED_END']) || !empty($data['END'])) {
            try {
                $endDateValue = $data['PROJECTED_END'] ?? $data['END'];
                $endDate = $this->parseLegacyDate($endDateValue);
                if ($endDate) {
                    $payload['endDate'] = $endDate->format('Y-m-d');
                    $payload['dates'][] = [
                        'type' => 'endDate',
                        'date' => $endDate->format('Y-m-d')
                    ];
                }
            } catch (\Exception $e) {
                // Invalid date format, continue with default
                $payload['dates'][] = [
                    'type' => 'endDate',
                    'date' => ''
                ];
            }
        } else {
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
                    $totalProjectCost = (float) $data['COST'];
                } else {
                    $cleanValue = trim(str_replace('€', '', $data['COST']));
                    $cleanValue = str_replace('.', '', $cleanValue);
                    $cleanValue = str_replace(',', '.', $cleanValue);
                    $totalProjectCost = (float) $cleanValue;
                }

                $payload['projectCosts'] = $totalProjectCost;
                
                $gapFundingPercentage = 0;
                $externalFundingPercentage = 100;
                
                if (!empty($data['FUNDING'])) {
                    $fundingAmount = 0;
                    if (is_numeric($data['FUNDING'])) {
                        $fundingAmount = (float) $data['FUNDING'];
                    } else {
                        $cleanValue = trim(str_replace('€', '', $data['FUNDING']));
                        $cleanValue = str_replace('.', '', $cleanValue);
                        $cleanValue = str_replace(',', '.', $cleanValue);
                        $fundingAmount = (float) $cleanValue;
                    }
                    
                    if ($totalProjectCost > 0) {
                        $gapFundingPercentage = round(($fundingAmount / $totalProjectCost) * 100, 2);
                        $gapFundingPercentage = min($gapFundingPercentage, 100);
                        $externalFundingPercentage = round(100 - $gapFundingPercentage, 2);
                    }
                }
                
                $payload['financing'] = [
                    [
                        'value' => (string)$gapFundingPercentage,
                        'id' => 'costsGap'
                    ],
                    [
                        'value' => '0',
                        'id' => 'costsPrivate'
                    ],
                    [
                        'value' => (string)$externalFundingPercentage,
                        'id' => 'costsExternal'
                    ]
                ];
            } catch (\Exception $e) {
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
            if (strpos($period, '14') !== false || strpos($period, '20') !== false) {
                $periodID = 1;
            } elseif (strpos($period, '07') !== false || strpos($period, '13') !== false) {
                $periodID = 4;
            }
            
            $lePeriod = $this->em->getRepository(\App\Entity\LEPeriod::class)
                ->findOneBy(['id' => $periodID]);

            if (!$lePeriod) {
                $allPeriods = $this->em->getRepository(\App\Entity\LEPeriod::class)->findAll();
                foreach ($allPeriods as $p) {
                    $periodName = strtolower($p->getName());
                    
                    if ((strpos($period, '14') !== false || strpos($period, '20') !== false) &&
                        (strpos($periodName, '14') !== false || strpos($periodName, '20') !== false)) {
                        $lePeriod = $p;
                        break;
                    } elseif ((strpos($period, '07') !== false || strpos($period, '13') !== false) &&
                        (strpos($periodName, '07') !== false || strpos($periodName, '13') !== false)) {
                        $lePeriod = $p;
                        break;
                    }
                }
            }
            
            if ($lePeriod) {
                $payload['lePeriod'] = $lePeriod->getId();
                
                if($data['PLAN1'] && ($lePeriod->getId() === 1 || strpos($period, '14') !== false)) {
                    $plan1Value = trim($data['PLAN1']);
                    
                    if (preg_match('/^(\d{1,2})\s+(.+)$/', $plan1Value, $matches)) {
                        $numericPrefix = $matches[1];
                        $articleName = $matches[2];
                        
                        if (strlen($numericPrefix) === 1) {
                            $numericPrefix = '0' . $numericPrefix;
                        }
                        
                        $allArticles = $this->em->getRepository(\App\Entity\LEFundingArticle::class)->findAll();
                        $matchingArticle = null;
                        
                        foreach ($allArticles as $article) {
                            $articleNameDb = $article->getName();
                            similar_text(strtolower($articleName), strtolower($articleNameDb), $similarity);
                            
                            if ($similarity >= 80) {
                                $matchingArticle = $article;
                                break;
                            }
                        }
                        
                        if ($matchingArticle) {
                            $allCategories = $this->em->getRepository(\App\Entity\LEFundingCategory::class)->findAll();
                            $matchingCategory = null;
                            
                            foreach ($allCategories as $category) {
                                $categoryName = $category->getName();
                                
                                if (stripos($categoryName, 'M' . $numericPrefix) !== false) {
                                    $matchingCategory = $category;
                                    break;
                                }
                            }
                            
                            if ($matchingArticle && $matchingCategory) {
                                $payload['leFundingArticle'] = $matchingArticle->getId();
                                $payload['leFundingCategory'] = $matchingCategory->getId();
                            } else if ($matchingArticle) {
                                $articleCategory = $matchingArticle->getCategory();
                                if ($articleCategory) {
                                    $payload['leFundingArticle'] = $matchingArticle->getId();
                                    $payload['leFundingCategory'] = $articleCategory->getId();
                                }
                            }
                        }
                    }
                }
                
                // Process PLAN3 data to extract the funding method
                if (!empty($data['PLAN3'])) {
                    $plan3Value = trim($data['PLAN3']);
                    
                    if (isset($payload['leFundingArticle'])) {
                        $fundingArticleId = $payload['leFundingArticle'];
                        $fundingArticle = $this->em->getRepository(\App\Entity\LEFundingArticle::class)->find($fundingArticleId);
                        
                        if ($fundingArticle) {
                            $matchingMethod = null;
                            
                            $methodRepository = $this->em->getRepository(\App\Entity\LEFundingMethod::class);
                            $existingMethods = $methodRepository->findAll();
                            
                            foreach ($existingMethods as $method) {
                                if (strtolower(trim($method->getName())) === strtolower(trim($plan3Value))) {
                                    $matchingMethod = $method;
                                    break;
                                }
                            }
                            
                            if (!$matchingMethod) {
                                $bestMatch = null;
                                $bestSimilarity = 0;
                                
                                foreach ($existingMethods as $method) {
                                    $methodName = $method->getName();
                                    $plan3Name = $plan3Value;
                                    
                                    $normalizedMethodName = preg_replace('/^[\d\.\s]+[a-z]\)\s*/', '', $methodName);
                                    $normalizedPlan3Name = preg_replace('/^[\d\.\s]+[a-z]\)\s*/', '', $plan3Name);
                                    
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
                                }
                            }
                            
                            if (!$matchingMethod) {
                                $newMethod = new \App\Entity\LEFundingMethod();
                                $newMethod->setName($plan3Value);
                                $newMethod->setArticle($fundingArticle);
                                
                                $this->em->persist($newMethod);
                                $this->em->flush();
                                
                                $matchingMethod = $newMethod;
                            }
                            
                            if ($matchingMethod) {
                                $payload['leFundingMethod'] = $matchingMethod->getId();
                            }
                        }
                    }
                }
            } else {
                $payload['lePeriodName'] = $data['PERIOD']; // Keep this for reference
            }
        }

        // Process contact information
        if (!empty($data['CONTACT']) || !empty($data['LEAD_PARTNER'])) {
            $postalCode = '';
            $city = '';
            $street = '';
            
            if (!empty($data['ADDRESS'])) {
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

        if (!empty($data['URL'])) {
            $payload['links'][] = [
                'url' => $data['URL'],
                'title' => 'Website',
            ];
        }

        if (!empty($data['INITIALPOSITION'])) {
            $payload['initialContext'] = $data['INITIALPOSITION'];
        }

        if (!empty($data['TARGET'])) {
            $payload['initialContextGoals'] = (string)$data['TARGET'];
        }

        if (!empty($data['IMPLEMENTATION'])) {
            $payload['fundingMethod'] = $data['IMPLEMENTATION'];
        }

        if (!empty($data['RESULT'])) {
            $payload['resultsQuantity'] = (string)$data['RESULT'];
        }

        if (!empty($data['EXPERIENCE'])) {
            $payload['learningExperience'] = $data['EXPERIENCE'];
        }

        // Process geographic regions
        $regions = ['Burgenland', 'Kärnten', 'Niederösterreich', 'Oberösterreich', 
                   'Salzburg', 'Steiermark', 'Tirol', 'Vorarlberg', 'Wien'];
        
        foreach ($regions as $region) {
            if (isset($data[$region]) && $data[$region] === 'ja') {
                $regionEntity = $this->em->getRepository(\App\Entity\GeographicRegion::class)
                    ->findOneBy(['name' => $region]);
                
                if ($regionEntity) {
                    $payload['geographicRegions'][] = [
                        'id' => $regionEntity->getId(),
                        'name' => $region
                    ];
                } else {
                    $payload['geographicRegions'][] = ['name' => $region];
                }
            }
        }

        // Process states (Bundesländer)
        $states = ['Burgenland', 'Kärnten', 'Niederösterreich', 'Oberösterreich', 
                   'Salzburg', 'Steiermark', 'Tirol', 'Vorarlberg', 'Wien'];
        
        foreach ($states as $state) {
            if (isset($data[$state]) && $data[$state] === 'ja') {
                $stateEntity = $this->em->getRepository(\App\Entity\State::class)
                    ->findOneBy(['name' => $state]);
                
                if ($stateEntity) {
                    $payload['states'][] = [
                        'id' => $stateEntity->getId(),
                        'name' => $state
                    ];
                } else {
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
        $lagMapping = $this->getLagMapping();
        $validLags = [];
        
        foreach ($lagMapping as $excelColumn => $lagName) {
            if (isset($data[$excelColumn]) && $data[$excelColumn] === 'ja') {
                $localWorkgroup = $this->em->getRepository(\App\Entity\LocalWorkgroup::class)
                    ->findOneBy(['name' => $lagName]);
                
                if ($localWorkgroup) {
                    $validLags[] = [
                        'id' => $localWorkgroup->getId(),
                        'name' => $lagName
                    ];
                } else {
                    $localWorkgroups = $this->em->getRepository(\App\Entity\LocalWorkgroup::class)
                        ->createQueryBuilder('l')
                        ->where('l.name LIKE :name')
                        ->setParameter('name', '%' . $lagName . '%')
                        ->getQuery()
                        ->getResult();
                    
                    if (count($localWorkgroups) > 0) {
                        $localWorkgroup = $localWorkgroups[0];
                        $validLags[] = [
                            'id' => $localWorkgroup->getId(),
                            'name' => $localWorkgroup->getName()
                        ];
                        $payload['cooperationProjectAt'] = true;
                    } else {
                        $payload['localWorkgroups'][] = ['name' => $lagName];
                    }
                }
            }
        }
        
        foreach ($validLags as $lag) {
            $payload['localWorkgroups'][] = $lag;
        }
        
        if (!empty($validLags)) {
            $payload['localWorkgroup'] = $validLags[0]['id'];
        }
    }

    /**
     * Process topics and tags from legacy Excel data
     */
    private function processTopicsFromLegacyExcel(array $data, array &$payload): void
    {
        $allTopicsInDB = $this->em->getRepository(\App\Entity\Topic::class)->findAll();
        $topicNamesInDB = [];
        foreach ($allTopicsInDB as $dbTopic) {
            $topicNamesInDB[] = $dbTopic->getName();
        }
        
        $topicMapping = $this->getTopicMapping();
        
        foreach ($topicMapping as $excelColumn => $topic) {
            if (isset($data[$excelColumn]) && $data[$excelColumn] === 'ja') {
                $topicEntity = $this->em->getRepository(\App\Entity\Topic::class)
                    ->findOneBy(['name' => $topic]);
                
                if ($topicEntity) {
                    $payload['topics'][] = [
                        'id' => $topicEntity->getId(),
                        'name' => $topicEntity->getName()
                    ];
                } else {
                    $topicEntities = $this->em->getRepository(\App\Entity\Topic::class)
                        ->createQueryBuilder('t')
                        ->where('t.name LIKE :name')
                        ->setParameter('name', '%' . $topic . '%')
                        ->getQuery()
                        ->getResult();
                    
                    if (count($topicEntities) > 0) {
                        $topicEntity = $topicEntities[0];
                        $payload['topics'][] = [
                            'id' => $topicEntity->getId(),
                            'name' => $topicEntity->getName()
                        ];
                    } else {
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
        $string = transliterator_transliterate('Any-Latin; Latin-ASCII', $string);
        $string = mb_strtolower(trim($string));
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
            'LAG Attersee - Attergau REGATTA' => 'LAG Attersee - Attergau REGATTA',
            'LAG Biosphärenpark Lungau' => 'LAG Biosphäre Lungau',
            'LAG Bucklige Welt -Wechselland' => 'LAG Bucklige Welt -Wechselland',
            'LAG Donau Niederösterreich-Mitte' => 'LAG Donau Niederösterreich-Mitte',
            'LAG Donau-Böhmerwald' => 'LAG Donau-Böhmerwald',
            'LAG Obst- und Gemüseregion Eferding' => 'LAG Eferdinger Land',
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
            'LAG REGIS Regionalentwicklung Inneres Salzkammergut' => 'LAG Kulturerbe Salzkammergut REGIS',
            'LAG LEADER Mitten im Innviertel' => 'LAG LEADER Mitten im Innviertel',
            'LAG Lebenswert Pongau-Tennengau' => 'LAG Lebens.Wert.Pongau',
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
            'LAG Zukunft Oberinnviertel-Mattigtal' => 'LAG Oberinnviertel-Mattigtal',
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
            'LAG Waldviertler Wohlviertel' => 'LAG Waldviertler Wohlviertel Region Nationalpark Thayaland',
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
            'Klimaschutz' => 'Klimaschutz',
            'Lebensmittelverarbeitung und Kulinarik' => 'Kulinarik',
            'nachhaltige Land- und Forstwirtschaft' => 'Land- und Forstwirtschaft inkl. Wertschöpfungskette',
            'Umwelt und Biodiversität' => 'Umwelt, Biodiversität, Naturschutz',
            'Klimawandelanpassung' => 'Klimawandelanpassung',
            'Gleichstellung' => 'Chancengleichheit',
            'Bildung, Sensibilisierung und Wissenstransfer' => 'Bildung & Lebenslanges Lernen',
            'nachhaltige Land- und Forstwirtschaft' => 'Landwirtschaft',
            'Umwelt und Biodiversität' => 'Luftreinhaltung',
            'ländliche Wirtschaft/ KMU' => 'Wertschöpfung',
            'LEADER' => 'LEADER',
            'Umwelt und Biodiversität' => 'Umweltschutz',
            'Bildung, Sensibilisierung und Wissenstransfer' => 'Wissenstransfer',
            'ländliche Wirtschaft/ KMU' => 'Kurze Versorgungsketten',
            'Gleichstellung' => 'Frauen',
            'Gemeinwohl und Daseinsvorsorge' => 'Interkommunale Kooperation',
            'nachhaltige Land- und Forstwirtschaft' => 'Forstwirtschaft',
            'ländliche Wirtschaft/ KMU' => 'Diversifizierung',
            'Klimaschutz' => 'Erneuerbare Energie',
            'Naturschutz' => 'Naturschutz',
            'nachhaltige Land- und Forstwirtschaft' => 'Wald',
            'Gemeinwohl und Daseinsvorsorge' => 'Lokale Agenda 21',
            'Gleichstellung' => 'Gender',
            'Jugend' => 'Jugend',
            'Vermarktung und Vertrieb' => 'Direktvermarktung',
            'Umwelt und Biodiversität' => 'Biodiversität',
            'Gemeinwohl und Daseinsvorsorge' => 'Gemeindeentwicklung',
            'Klimaschutz' => 'Energieeffizienz',
            'Umwelt und Biodiversität' => 'Boden',
            'Bildung, Sensibilisierung und Wissenstransfer' => 'EIP Europäische Innovationspartnerschaft',
            'ländliche Wirtschaft/ KMU' => 'Standortentwicklung',
            'nachhaltige Land- und Forstwirtschaft' => 'Tierwohl',
            'Umwelt und Biodiversität' => 'Schutzgebiete',
            'Tourismus' => 'Tourismus',
            'Kultur' => 'Kultur',
            'ländliche Wirtschaft/ KMU' => 'Betriebswirtschaft',
            'Gemeinwohl und Daseinsvorsorge' => 'Integration & Soziale Inklusion',
            'nachhaltige Land- und Forstwirtschaft' => 'Alm- & Berglandwirtschaft',
            'Umwelt und Biodiversität' => 'ÖPUL',
            'nachhaltige Land- und Forstwirtschaft' => 'Risikomanagement',
            'ländliche Wirtschaft/ KMU' => 'Leerstand',
            'Gemeinwohl und Daseinsvorsorge' => 'Soziale Dienstleistungen',
            'Umwelt und Biodiversität' => 'Wasser',
            'Vermarktung und Vertrieb' => 'Vermarktung und Vertrieb',
            'Mobilität' => 'Mobilität',
            'Gemeinwohl und Daseinsvorsorge' => 'Gesundheit',
            'ländliche Wirtschaft/ KMU' => 'Landwirtschaftliche Dienstleistungen',
            'Vermarktung und Vertrieb' => 'Lebensmittelverarbeitung',
            'ländliche Wirtschaft/ KMU' => 'KMUs, Gewerbe & Wirtschaft',
            'Gemeinwohl und Daseinsvorsorge' => 'Nahversorgung',
            'Gemeinwohl und Daseinsvorsorge' => 'Gastronomie',
            'Gemeinwohl und Daseinsvorsorge' => 'Gemeinschaftsverpflegung',
            'Vermarktung und Vertrieb' => 'Handel',
            'nachhaltige Land- und Forstwirtschaft' => 'Nachhaltige Landschaftspflege'
        ];
    }

    /**
     * Generate a preview of the import data
     */
    public function generatePreview(ProjectImport $import): array
    {
        try {
            $filePath = $import->getFilePath();
            
            $spreadsheet = IOFactory::load($filePath);
            $worksheet = $spreadsheet->getActiveSheet();
            
            $highestRow = $worksheet->getHighestRow();
            $highestColumn = $worksheet->getHighestColumn();
            $highestColumnIndex = Coordinate::columnIndexFromString($highestColumn);
            
            $headerRowIndex = $this->getHeaderRowCount();
            
            $headers = [];
            for ($col = 1; $col <= $highestColumnIndex; $col++) {
                $columnLetter = Coordinate::stringFromColumnIndex($col);
                $header = $worksheet->getCellByColumnAndRow($col, $headerRowIndex)->getValue();
                if (!empty($header)) {
                    $headers[$columnLetter] = $header;
                }
            }
            
            $maxPreviewRows = 100;
            $rowLimit = min($highestRow, $maxPreviewRows + $headerRowIndex);
            
            $preview = [];
            for ($rowIndex = $headerRowIndex + 1; $rowIndex <= $rowLimit; $rowIndex++) {
                $rowData = [];
                
                for ($col = 1; $col <= $highestColumnIndex; $col++) {
                    $columnLetter = Coordinate::stringFromColumnIndex($col);
                    if (isset($headers[$columnLetter])) {
                        $header = $headers[$columnLetter];
                        $value = $worksheet->getCellByColumnAndRow($col, $rowIndex)->getValue();
                        $rowData[$header] = $value;
                    }
                }
                
                if (empty($rowData)) {
                    continue;
                }
                
                $payload = $this->preparePreviewPayload($rowData);
                
                $previewItem = [
                    'rowIndex' => $rowIndex,
                    'title' => $payload['title'] ?? 'Kein Titel',
                    'description' => mb_substr($payload['description'] ?? '', 0, 100) . (strlen($payload['description'] ?? '') > 100 ? '...' : ''),
                    'projectCode' => $payload['projectCode'] ?? '',
                    'startDate' => $payload['startDate'] ?? null,
                    'endDate' => $payload['endDate'] ?? null,
                    'status' => 'valid',
                    'message' => '',
                    'data' => $payload
                ];
                
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
            
            $import->setTotalRows($highestRow - $headerRowIndex);
            $this->em->persist($import);
            $this->em->flush();
            
            return $preview;
        } catch (\Exception $e) {
            return [];
        }
    }
    
    /**
     * Prepare a simplified payload for preview
     */
    private function preparePreviewPayload(array $data): array
    {
        $fullPayload = $this->prepareProjectPayload($data);
        
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
            'dates' => $fullPayload['dates'] ?? [],
            'source' => $fullPayload['source'] ?? 'legacy_import'
        ];
    }

    /**
     * Import projects from the Excel file
     */
    public function importProjects(ProjectImport $import, User $user, ?LEPeriod $lePeriod = null): bool
    {
        try {
            $import->setStatus('processing');
            $import->setUpdatedAt(new \DateTime());
            $this->em->persist($import);
            $this->em->flush();
            
            $filePath = $import->getFilePath();
            
            if (!file_exists($filePath)) {
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
            
            try {
                $spreadsheet = IOFactory::load($filePath);
            } catch (\Exception $e) {
                throw new \Exception('Failed to load Excel file: ' . $e->getMessage());
            }
            
            $worksheet = $spreadsheet->getActiveSheet();
            
            $highestRow = $worksheet->getHighestRow();
            $highestColumn = $worksheet->getHighestColumn();
            $highestColumnIndex = Coordinate::columnIndexFromString($highestColumn);
            
            $headerRowIndex = $this->getHeaderRowCount();
            
            $headers = [];
            for ($col = 1; $col <= $highestColumnIndex; $col++) {
                $columnLetter = Coordinate::stringFromColumnIndex($col);
                $header = $worksheet->getCellByColumnAndRow($col, $headerRowIndex)->getValue();
                if (!empty($header)) {
                    $headers[$columnLetter] = $header;
                }
            }
            
            $processedRows = 0;
            $successfulRows = 0;
            $errorRows = 0;
            $errors = [];
            
            for ($rowIndex = $headerRowIndex + 1; $rowIndex <= $highestRow; $rowIndex++) {
                $rowData = [];
                
                for ($col = 1; $col <= $highestColumnIndex; $col++) {
                    $columnLetter = Coordinate::stringFromColumnIndex($col);
                    if (isset($headers[$columnLetter])) {
                        $header = $headers[$columnLetter];
                        $value = $worksheet->getCellByColumnAndRow($col, $rowIndex)->getValue();
                        $rowData[$header] = $value;
                    }
                }
                
                if (empty($rowData['TITLE'])) {
                    continue;
                }
                
                $processedRows++;
                
                try {
                    $result = $this->processImportItem($import, $rowIndex);
                } catch (\Exception $e) {
                    $errorRows++;
                    $errors[] = 'Fehler in Zeile ' . $rowIndex . ': ' . $e->getMessage();
                    continue;
                }
                
                if ($result['status'] === 'success') {
                    $projectData = $result['data'];
                    
                    try {
                        $this->validatePayloadStructure($projectData);

                        $existingProject = $this->findProjectByTitle($projectData['title']);
                        
                        if ($existingProject) {
                            $project = $this->projectService->updateProject($existingProject, $projectData);
                        } else {
                            $project = $this->projectService->createProject($projectData);
                        }
                        
                        if ($lePeriod) {
                            $project->setLePeriod($lePeriod);
                        } elseif (!empty($projectData['lePeriodName'])) {
                            $lePeriodRepo = $this->em->getRepository(LEPeriod::class);
                            $foundLePeriod = $lePeriodRepo->findOneBy(['name' => $projectData['lePeriodName']]);
                            if ($foundLePeriod) {
                                $project->setLePeriod($foundLePeriod);
                            }
                        }
                        
                        $project->setSource('legacy_import');
                        
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
        
        foreach ($stringFields as $field) {
            if (isset($payload[$field]) && !is_string($payload[$field])) {
                $payload[$field] = (string)$payload[$field];
            } elseif (!isset($payload[$field])) {
                $payload[$field] = '';
            }
        }
        
        foreach ($arrayFields as $field) {
            if (!isset($payload[$field])) {
                $payload[$field] = [];
            } elseif (!is_array($payload[$field])) {
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
        
        foreach ($booleanFields as $field) {
            if (isset($payload[$field]) && !is_bool($payload[$field])) {
                $payload[$field] = (bool)$payload[$field];
            } elseif (!isset($payload[$field])) {
                $payload[$field] = false;
            }
        }
        
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
        
        $entityFields = [
            'localWorkgroups', 'topics', 'tags', 'geographicRegions', 'countries', 'states',
            'programs', 'instruments', 'businessSectors', 'synergyFundTags', 'synergyGoalTags'
        ];
        
        foreach ($entityFields as $field) {
            if (isset($payload[$field]) && is_array($payload[$field])) {
                foreach ($payload[$field] as $key => $item) {
                    if (is_string($item)) {
                        $payload[$field][$key] = ['name' => $item];
                    } elseif (is_array($item) && !isset($item['name']) && !isset($item['id'])) {
                        unset($payload[$field][$key]);
                    }
                }
                $payload[$field] = array_values($payload[$field]);
            }
        }
        
        if (isset($payload['localWorkgroups']) && is_array($payload['localWorkgroups'])) {
            foreach ($payload['localWorkgroups'] as $key => $item) {
                if ((!isset($item['id']) || empty($item['id'])) && isset($item['name']) && !empty($item['name'])) {
                    $localWorkgroup = $this->em->getRepository(\App\Entity\LocalWorkgroup::class)
                        ->findOneBy(['name' => $item['name']]);
                    
                    if ($localWorkgroup) {
                        $payload['localWorkgroups'][$key]['id'] = $localWorkgroup->getId();
                    } else {
                        unset($payload['localWorkgroups'][$key]);
                    }
                }
            }
            $payload['localWorkgroups'] = array_values($payload['localWorkgroups']);
        }
        
        if (isset($payload['topics']) && is_array($payload['topics'])) {
            foreach ($payload['topics'] as $key => $item) {
                if ((!isset($item['id']) || empty($item['id'])) && isset($item['name']) && !empty($item['name'])) {
                    $topic = $this->em->getRepository(\App\Entity\Topic::class)
                        ->findOneBy(['name' => $item['name']]);
                    
                    if ($topic) {
                        $payload['topics'][$key]['id'] = $topic->getId();
                    } else {
                        $topicEntities = $this->em->getRepository(\App\Entity\Topic::class)
                            ->createQueryBuilder('t')
                            ->where('t.name LIKE :name')
                            ->setParameter('name', '%' . $item['name'] . '%')
                            ->getQuery()
                            ->getResult();
                        
                        if (count($topicEntities) > 0) {
                            $topic = $topicEntities[0];
                            $payload['topics'][$key]['id'] = $topic->getId();
                        } else {
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
                                unset($payload['topics'][$key]);
                            }
                        }
                    }
                }
            }
            $payload['topics'] = array_values($payload['topics']);
        }
        
        if (isset($payload['geographicRegions']) && is_array($payload['geographicRegions'])) {
            foreach ($payload['geographicRegions'] as $key => $item) {
                if ((!isset($item['id']) || empty($item['id'])) && isset($item['name']) && !empty($item['name'])) {
                    $region = $this->em->getRepository(\App\Entity\GeographicRegion::class)
                        ->findOneBy(['name' => $item['name']]);
                    
                    if ($region) {
                        $payload['geographicRegions'][$key]['id'] = $region->getId();
                    } else {
                        unset($payload['geographicRegions'][$key]);
                    }
                }
            }
            $payload['geographicRegions'] = array_values($payload['geographicRegions']);
        }
        
        if (isset($payload['states']) && is_array($payload['states'])) {
            foreach ($payload['states'] as $key => $item) {
                if ((!isset($item['id']) || empty($item['id'])) && isset($item['name']) && !empty($item['name'])) {
                    $state = $this->em->getRepository(\App\Entity\State::class)
                        ->findOneBy(['name' => $item['name']]);
                    
                    if ($state) {
                        $payload['states'][$key]['id'] = $state->getId();
                    } else {
                        unset($payload['states'][$key]);
                    }
                }
            }
            $payload['states'] = array_values($payload['states']);
        }
    }

    /**
     * Parse an address string into components (postal code, city, street)
     * 
     * Handles different formats:
     * - "Postal code City, Street" (e.g., "9991 Dölsach, Stribach 97")
     * - "Street, Postal code City" (e.g., "Innsbrucker Straße 77, 6380 St. Johann i. T.")
     * 
     * @param string $address The full address string
     * @return array Associative array with postalCode, city, and street
     */
    private function parseAddress(string $address): array
    {
        $result = [
            'postalCode' => '',
            'city' => '',
            'street' => ''
        ];
        
        $address = trim($address);
        
        if (empty($address)) {
            return $result;
        }
        
        $postalCodePattern = '/\b\d{4,5}\b/';
        
        if (preg_match($postalCodePattern, $address, $matches)) {
            $postalCode = $matches[0];
            
            if (strpos($address, $postalCode) === 0) {
                if (preg_match('/^\d{4,5}\s+([^,]+),\s*(.+)$/', $address, $matches)) {
                    $result['postalCode'] = $postalCode;
                    $result['city'] = trim($matches[1]);
                    $result['street'] = trim($matches[2]);
                } else {
                    if (preg_match('/^\d{4,5}\s+(.+)$/', $address, $matches)) {
                        $result['postalCode'] = $postalCode;
                        $result['city'] = trim($matches[1]);
                        $result['street'] = '';
                    }
                }
            } else {
                $parts = explode(',', $address);
                
                if (count($parts) >= 2) {
                    $result['street'] = trim($parts[0]);
                    
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
            $parts = explode(',', $address);
            
            if (count($parts) >= 2) {
                $result['street'] = trim($parts[0]);
                $result['city'] = trim(end($parts));
            } else {
                $result['street'] = $address;
            }
        }
        
        return $result;
    }

    /**
     * Parse a legacy date value into a DateTime object.
     * 
     * Handles various formats:
     * - Year only (e.g. "2019" or 2019)
     * - Full date string
     * - DateTime object
     * - Excel date number
     * 
     * @param mixed $value The date value to parse
     * @return \DateTime|null The parsed date or null if parsing failed
     */
    private function parseLegacyDate($value): ?\DateTime
    {
        if (empty($value)) {
            return null;
        }

        if ($value instanceof \DateTime) {
            return $value;
        }

        if (is_string($value)) {
            if (preg_match('/^(\d{4})$/', trim($value), $matches)) {
                return new \DateTime($matches[1] . '-01-01');
            }
            return new \DateTime($value);
        }

        if (is_numeric($value)) {
            if ($value >= 1900 && $value <= 2100) {
                return new \DateTime((int)$value . '-01-01');
            }
            return \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($value);
        }

        return null;
    }
} 