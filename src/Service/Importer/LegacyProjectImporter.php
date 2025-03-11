<?php

namespace App\Service\Importer;

use App\Entity\ProjectImport;
use App\Entity\User;
use App\Entity\LEPeriod;
use App\Service\ProjectService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\String\Slugger\SluggerInterface;

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
        return 'Alte Projekte Projektimport';
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
        // To be implemented based on the legacy Excel structure
        return 1;
    }

    /**
     * {@inheritdoc}
     */
    public function processImportItem(ProjectImport $import, int $rowIndex): array
    {
        // To be implemented based on the legacy Excel structure
        return [
            'status' => 'error',
            'message' => 'Legacy project import not yet implemented'
        ];
    }

    /**
     * {@inheritdoc}
     */
    protected function prepareProjectPayload(array $data): array
    {
        // To be implemented based on the legacy Excel structure
        return [
            'title' => 'Not implemented',
            'description' => 'Legacy project import not yet implemented'
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function generatePreview(ProjectImport $import): array
    {
        // To be implemented based on the legacy Excel structure
        return [];
    }

    /**
     * {@inheritdoc}
     */
    public function importProjects(ProjectImport $import, User $user, ?LEPeriod $lePeriod = null): bool
    {
        // To be implemented based on the legacy Excel structure
        $import->setStatus(ProjectImport::STATUS_FAILED);
        $import->setErrorMessage('Legacy project import not yet implemented');
        $import->setUpdatedAt(new \DateTime());
        $this->em->persist($import);
        $this->em->flush();
        
        return false;
    }
} 