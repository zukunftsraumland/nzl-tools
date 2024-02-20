<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use OpenApi\Attributes as OA;

/**
 * Employment
 */
#[ORM\Table(name: 'pv_employment')]
#[ORM\Entity(repositoryClass: 'App\Repository\EmploymentRepository')]
class Employment
{

    #[ORM\Column(name: 'id', type: 'integer')]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    #[Groups(['id', 'employment'])]
    private $id;

    #[ORM\Column(name: 'created_at', type: 'datetime')]
    #[Groups(['employment'])]
    private $createdAt;

    #[ORM\Column(name: 'updated_at', type: 'datetime', nullable: true)]
    #[Groups(['employment'])]
    private $updatedAt;

    #[ORM\Column(name: 'position', type: 'integer', nullable: true)]
    #[Groups(['employment'])]
    private $position;

    #[ORM\Column(name: 'role', type: 'string', length: 128, nullable: true)]
    #[Groups(['employment'])]
    private $role;

    #[ORM\ManyToOne(targetEntity: 'Contact')]
    #[ORM\JoinColumn(name: 'company_id', referencedColumnName: 'id')]
    #[Groups(['employment'])]
    private ?Contact $company = null;

    #[ORM\ManyToOne(targetEntity: 'Contact')]
    #[ORM\JoinColumn(name: 'employee_id', referencedColumnName: 'id')]
    #[Groups(['employment'])]
    private ?Contact $employee = null;

    /**
     * Get id
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set createdAt
     *
     * @param \DateTime $createdAt
     *
     * @return Employment
     */
    public function setCreatedAt($createdAt)
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    /**
     * Get createdAt
     *
     * @return \DateTime
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * Set updatedAt
     *
     * @param \DateTime $updatedAt
     *
     * @return Employment
     */
    public function setUpdatedAt($updatedAt)
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    /**
     * Get updatedAt
     *
     * @return \DateTime
     */
    public function getUpdatedAt()
    {
        return $this->updatedAt;
    }

    /**
     * Set position
     *
     * @param int|null $position
     *
     * @return Employment
     */
    public function setPosition($position)
    {
        $this->position = $position;

        return $this;
    }

    /**
     * Get position
     *
     * @return int|null
     */
    public function getPosition()
    {
        return $this->position;
    }

    /**
     * Set role
     *
     * @param string $role
     *
     * @return Employment
     */
    public function setRole($role)
    {
        $this->role = $role;

        return $this;
    }

    /**
     * Get role
     *
     * @return string
     */
    public function getRole()
    {
        return $this->role;
    }

    /**
     * Set translations
     *
     * @param array $translations
     *
     * @return Employment
     */
    public function setTranslations($translations)
    {
        $this->translations = $translations;

        return $this;
    }

    /**
     * Get translations
     *
     * @return array
     */
    public function getTranslations()
    {
        return $this->translations;
    }

    public function getCompany(): ?Contact
    {
        return $this->company;
    }

    public function setCompany(?Contact $company): self
    {
        $this->company = $company;

        return $this;
    }

    public function getEmployee(): ?Contact
    {
        return $this->employee;
    }

    public function setEmployee(?Contact $employee): self
    {
        $this->employee = $employee;

        return $this;
    }
}

