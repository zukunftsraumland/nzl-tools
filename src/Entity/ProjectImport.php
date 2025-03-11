<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * ProjectImport
 * 
 * Tracks import sessions for projects
 */
#[ORM\Table(name: 'pv_project_import')]
#[ORM\Entity]
class ProjectImport
{
    /**
     * Import statuses
     */
    const STATUS_PENDING = 'pending';
    const STATUS_PROCESSING = 'processing';
    const STATUS_COMPLETED = 'completed';
    const STATUS_FAILED = 'failed';

    #[ORM\Column(name: 'id', type: 'integer')]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    #[Groups(['id', 'project_import'])]
    private $id;

    #[ORM\Column(name: 'filename', type: 'string', length: 255)]
    #[Groups(['project_import'])]
    private $filename;

    #[ORM\Column(name: 'original_filename', type: 'string', length: 255)]
    #[Groups(['project_import'])]
    private $originalFilename;

    #[ORM\Column(name: 'file_path', type: 'string', length: 255)]
    #[Groups(['project_import'])]
    private $filePath;

    #[ORM\Column(name: 'status', type: 'string', length: 20)]
    #[Groups(['project_import'])]
    private $status = self::STATUS_PENDING;

    #[ORM\Column(name: 'created_at', type: 'datetime')]
    #[Groups(['project_import'])]
    private $createdAt;

    #[ORM\Column(name: 'updated_at', type: 'datetime', nullable: true)]
    #[Groups(['project_import'])]
    private $updatedAt;

    #[ORM\Column(name: 'total_rows', type: 'integer', nullable: true)]
    #[Groups(['project_import'])]
    private $totalRows = 0;

    #[ORM\Column(name: 'processed_rows', type: 'integer', nullable: true)]
    #[Groups(['project_import'])]
    private $processedRows = 0;

    #[ORM\Column(name: 'successful_rows', type: 'integer', nullable: true)]
    #[Groups(['project_import'])]
    private $successfulRows = 0;

    #[ORM\Column(name: 'error_rows', type: 'integer', nullable: true)]
    #[Groups(['project_import'])]
    private $errorRows = 0;

    #[ORM\Column(name: 'error_message', type: 'text', nullable: true)]
    #[Groups(['project_import'])]
    private $errorMessage;

    #[ORM\Column(name: 'importer_type', type: 'string', length: 50, nullable: true)]
    #[Groups(['project_import'])]
    private $importerType = 'standard';

    #[ORM\OneToMany(mappedBy: 'import', targetEntity: ProjectImportItem::class, cascade: ['persist', 'remove'])]
    #[Groups(['project_import_details'])]
    private $items;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(name: 'user_id', referencedColumnName: 'id', nullable: true)]
    #[Groups(['project_import'])]
    private $user;

    public function __construct()
    {
        $this->items = new ArrayCollection();
        $this->createdAt = new \DateTime();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getFilename(): ?string
    {
        return $this->filename;
    }

    public function setFilename(string $filename): self
    {
        $this->filename = $filename;
        return $this;
    }

    public function getOriginalFilename(): ?string
    {
        return $this->originalFilename;
    }

    public function setOriginalFilename(string $originalFilename): self
    {
        $this->originalFilename = $originalFilename;
        return $this;
    }

    public function getFilePath(): ?string
    {
        return $this->filePath;
    }

    public function setFilePath(string $filePath): self
    {
        $this->filePath = $filePath;
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

    public function getTotalRows(): ?int
    {
        return $this->totalRows;
    }

    public function setTotalRows(?int $totalRows): self
    {
        $this->totalRows = $totalRows;
        return $this;
    }

    public function getProcessedRows(): ?int
    {
        return $this->processedRows;
    }

    public function setProcessedRows(?int $processedRows): self
    {
        $this->processedRows = $processedRows;
        return $this;
    }

    public function getSuccessfulRows(): ?int
    {
        return $this->successfulRows;
    }

    public function setSuccessfulRows(?int $successfulRows): self
    {
        $this->successfulRows = $successfulRows;
        return $this;
    }

    public function getErrorRows(): ?int
    {
        return $this->errorRows;
    }

    public function setErrorRows(?int $errorRows): self
    {
        $this->errorRows = $errorRows;
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

    public function getImporterType(): ?string
    {
        return $this->importerType;
    }

    public function setImporterType(?string $importerType): self
    {
        $this->importerType = $importerType;
        return $this;
    }

    /**
     * @return Collection|ProjectImportItem[]
     */
    public function getItems(): Collection
    {
        return $this->items;
    }

    public function addItem(ProjectImportItem $item): self
    {
        if (!$this->items->contains($item)) {
            $this->items[] = $item;
            $item->setImport($this);
        }

        return $this;
    }

    public function removeItem(ProjectImportItem $item): self
    {
        if ($this->items->removeElement($item)) {
            // set the owning side to null (unless already changed)
            if ($item->getImport() === $this) {
                $item->setImport(null);
            }
        }

        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): self
    {
        $this->user = $user;
        return $this;
    }

    /**
     * Get progress percentage
     */
    #[Groups(['project_import'])]
    public function getProgress(): int
    {
        if ($this->totalRows <= 0) {
            return 0;
        }
        
        return (int) (($this->processedRows / $this->totalRows) * 100);
    }
} 