<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * ProjectImportItem
 * 
 * Tracks individual items in a project import
 */
#[ORM\Table(name: 'pv_project_import_item')]
#[ORM\Entity]
class ProjectImportItem
{
    /**
     * Item statuses
     */
    const STATUS_PENDING = 'pending';
    const STATUS_PROCESSING = 'processing';
    const STATUS_COMPLETED = 'completed';
    const STATUS_FAILED = 'failed';
    const STATUS_SKIPPED = 'skipped';

    #[ORM\Column(name: 'id', type: 'integer')]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    #[Groups(['id', 'project_import_item'])]
    private $id;

    #[ORM\ManyToOne(targetEntity: ProjectImport::class, inversedBy: 'items')]
    #[ORM\JoinColumn(name: 'import_id', referencedColumnName: 'id', nullable: false)]
    private $import;

    #[ORM\Column(name: 'row_number', type: 'integer')]
    #[Groups(['project_import_item'])]
    private $rowNumber;

    #[ORM\Column(name: 'status', type: 'string', length: 20)]
    #[Groups(['project_import_item'])]
    private $status = self::STATUS_PENDING;

    #[ORM\Column(name: 'raw_data', type: 'json', nullable: true)]
    #[Groups(['project_import_item'])]
    private $rawData;

    #[ORM\Column(name: 'processed_data', type: 'json', nullable: true)]
    #[Groups(['project_import_item'])]
    private $processedData;

    #[ORM\Column(name: 'error_message', type: 'text', nullable: true)]
    #[Groups(['project_import_item'])]
    private $errorMessage;

    #[ORM\Column(name: 'created_at', type: 'datetime')]
    #[Groups(['project_import_item'])]
    private $createdAt;

    #[ORM\Column(name: 'updated_at', type: 'datetime', nullable: true)]
    #[Groups(['project_import_item'])]
    private $updatedAt;

    #[ORM\ManyToOne(targetEntity: Project::class)]
    #[ORM\JoinColumn(name: 'project_id', referencedColumnName: 'id', nullable: true)]
    #[Groups(['project_import_item'])]
    private $project;

    public function __construct()
    {
        $this->createdAt = new \DateTime();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getImport(): ?ProjectImport
    {
        return $this->import;
    }

    public function setImport(?ProjectImport $import): self
    {
        $this->import = $import;
        return $this;
    }

    public function getRowNumber(): ?int
    {
        return $this->rowNumber;
    }

    public function setRowNumber(int $rowNumber): self
    {
        $this->rowNumber = $rowNumber;
        return $this;
    }

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(string $status): self
    {
        $this->status = $status;
        return $this;
    }

    public function getRawData(): ?array
    {
        return $this->rawData;
    }

    public function setRawData(?array $rawData): self
    {
        $this->rawData = $rawData;
        return $this;
    }

    public function getProcessedData(): ?array
    {
        return $this->processedData;
    }

    public function setProcessedData(?array $processedData): self
    {
        $this->processedData = $processedData;
        return $this;
    }

    public function getErrorMessage(): ?string
    {
        return $this->errorMessage;
    }

    public function setErrorMessage(?string $errorMessage): self
    {
        $this->errorMessage = $errorMessage;
        return $this;
    }

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeInterface $createdAt): self
    {
        $this->createdAt = $createdAt;
        return $this;
    }

    public function getUpdatedAt(): ?\DateTimeInterface
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(?\DateTimeInterface $updatedAt): self
    {
        $this->updatedAt = $updatedAt;
        return $this;
    }

    public function getProject(): ?Project
    {
        return $this->project;
    }

    public function setProject(?Project $project): self
    {
        $this->project = $project;
        return $this;
    }
} 