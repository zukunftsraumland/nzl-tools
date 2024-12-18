<?php

namespace App\Entity;

use App\Repository\CommunitySubmissionRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CommunitySubmissionRepository::class)]
#[ORM\Table(name: 'pv_community_submission')]
class CommunitySubmission
{
    public const TYPE_PROJECT_CONTACT = 'project_contact';

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $verificationToken = null;

    #[ORM\Column(type: 'json')]
    private array $submissionData = [];

    #[ORM\Column(length: 255)]
    private ?string $email = null;

    #[ORM\Column(length: 50)]
    private ?string $type = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column]
    private bool $isVerified = false;

    public function __construct(string $type = self::TYPE_PROJECT_CONTACT)
    {
        $this->createdAt = new \DateTimeImmutable();
        $this->verificationToken = bin2hex(random_bytes(32));
        $this->type = $type;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getVerificationToken(): ?string
    {
        return $this->verificationToken;
    }

    public function setVerificationToken(string $verificationToken): self
    {
        $this->verificationToken = $verificationToken;
        return $this;
    }

    public function getSubmissionData(): array
    {
        return $this->submissionData;
    }

    public function setSubmissionData(array $submissionData): self
    {
        $this->submissionData = $submissionData;
        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;
        return $this;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(string $type): self
    {
        $this->type = $type;
        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function isVerified(): bool
    {
        return $this->isVerified;
    }

    public function setIsVerified(bool $isVerified): self
    {
        $this->isVerified = $isVerified;
        return $this;
    }
} 