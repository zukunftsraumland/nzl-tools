<?php

namespace App\Service\Importer;

use App\Service\ProjectService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\String\Slugger\SluggerInterface;

/**
 * Case Study Project Importer
 * 
 * This importer handles the case study project import format, which is similar to the standard format
 * but sets the caseStudy flag to true and may have additional case study specific fields.
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
        // Get the standard payload from the parent class
        $payload = parent::prepareProjectPayload($data);
        
        // Set the caseStudy flag to true
        $payload['caseStudy'] = true;
        
        // Add case study specific fields if they exist in the data
        $payload['exemplary'] = $data['Q13'] ?? '';
        $payload['initialContext'] = $data['Q14'] ?? '';
        $payload['initialContextGoals'] = $data['Q15'] ?? '';
        $payload['additionalValue'] = $data['Q16'] ?? '';
        $payload['additionalValueResult'] = $data['Q17'] ?? '';
        $payload['innovations'] = $data['Q18'] ?? '';
        $payload['integrationYoungCitizen'] = $data['Q19'] ?? '';
        $payload['integrationFemaleCitizen'] = $data['Q20'] ?? '';
        $payload['integrationMinorities'] = $data['Q21'] ?? '';
        $payload['learningExperience'] = $data['Q22'] ?? '';
        $payload['transferable'] = $data['Q23'] ?? '';
        $payload['transferDetails'] = $data['Q24'] ?? '';
        $payload['fundingMethodStakeholders'] = $data['Q25'] ?? '';
        $payload['resultsQuality'] = $data['Q26'] ?? '';
        $payload['resultsQuantity'] = $data['Q27'] ?? '';
        
        return $payload;
    }
} 