<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Table(name: 'pv_log')]
#[ORM\Entity(repositoryClass: 'App\Repository\LogRepository')]
#[ORM\Index(columns: ['context'], name: 'context_idx')]
#[ORM\Index(columns: ['category'], name: 'category_idx')]
#[ORM\Index(columns: ['action'], name: 'action_idx')]
#[ORM\Index(columns: ['referer'], name: 'referer_idx')]
#[ORM\Index(columns: ['username'], name: 'username_idx')]
#[ORM\Index(columns: ['fingerprint'], name: 'fingerprint_idx')]
class Log
{

    #[ORM\Column(name: 'id', type: 'bigint')]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    #[Groups(['id', 'log'])]
    private ?int $id;

    #[ORM\Column(name: 'created_at', type: 'datetime')]
    #[Groups(['log'])]
    private \DateTime $createdAt;

    #[ORM\Column(name: 'context', type: 'string', length: 64)]
    #[Groups(['log'])]
    private string $context;

    #[ORM\Column(name: 'category', type: 'string', length: 64, nullable: true)]
    #[Groups(['log'])]
    private ?string $category;

    #[ORM\Column(name: 'action', type: 'string', length: 64, nullable: true)]
    #[Groups(['log'])]
    private ?string $action;

    #[ORM\Column(name: 'value', type: 'text', nullable: true)]
    #[Groups(['log'])]
    private ?string $value;

    #[ORM\Column(name: 'referer', type: 'string', length: 768, nullable: true)]
    #[Groups(['log'])]
    private ?string $referer;

    #[ORM\Column(name: 'username', type: 'string', length: 255, nullable: true)]
    #[Groups(['log'])]
    private ?string $username;

    #[ORM\Column(name: 'fingerprint', type: 'string', length: 32, nullable: true)]
    #[Groups(['log'])]
    private ?string $fingerprint;

    public function getId()
    {
        return $this->id;
    }

    public function setCreatedAt(\DateTime $createdAt): self
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getCreatedAt(): \DateTime
    {
        return $this->createdAt;
    }

    public function setContext(string $context): self
    {
        $this->context = $context;

        return $this;
    }

    public function getContext(): string
    {
        return $this->context;
    }

    public function setCategory(?string $category): self
    {
        $this->category = $category;

        return $this;
    }

    public function getCategory(): ?string
    {
        return $this->category;
    }

    public function setAction(?string $action): self
    {
        $this->action = $action;

        return $this;
    }

    public function getAction(): ?string
    {
        return $this->action;
    }

    public function setValue(?string $value): self
    {
        $this->value = $value;

        return $this;
    }

    public function getValue(): ?string
    {
        return $this->value;
    }

    public function setReferer(?string $referer): self
    {
        $this->referer = $referer;

        return $this;
    }

    public function getReferer(): ?string
    {
        return $this->referer;
    }

    public function setUsername(?string $username): self
    {
        $this->username = $username;

        return $this;
    }

    public function getUsername(): ?string
    {
        return $this->username;
    }

    public function setFingerprint(?string $fingerprint): self
    {
        $this->fingerprint = $fingerprint;

        return $this;
    }

    public function getFingerprint(): ?string
    {
        return $this->fingerprint;
    }
}

