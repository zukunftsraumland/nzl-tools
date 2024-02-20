<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use OpenApi\Attributes as OA;

/**
 * City
 */
#[ORM\Table(name: 'pv_city')]
#[ORM\Entity(repositoryClass: 'App\Repository\CityRepository')]
class City
{

    #[ORM\Column(name: 'id', type: 'integer')]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    #[Groups(['id', 'city'])]
    private $id;

    #[ORM\Column(name: 'is_public', type: 'boolean')]
    #[Groups(['city'])]
    private $isPublic;

    #[ORM\Column(name: 'position', type: 'integer', nullable: true)]
    #[Groups(['city'])]
    private $position;

    #[ORM\Column(name: 'created_at', type: 'datetime')]
    #[Groups(['city'])]
    private $createdAt;

    #[ORM\Column(name: 'updated_at', type: 'datetime', nullable: true)]
    #[Groups(['city'])]
    private $updatedAt;

    #[ORM\Column(name: 'zip_code', type: 'string', length: 16, nullable: true)]
    #[Groups(['city'])]
    private $zipCode;

    #[ORM\Column(name: 'name', type: 'text', nullable: true)]
    #[Groups(['city'])]
    private $name;

    #[ORM\Column(name: 'municipal_number', type: 'integer', nullable: true)]
    #[Groups(['city'])]
    private $municipalNumber;

    #[ORM\Column(name: 'context', type: 'string', length: 255, nullable: true)]
    #[Groups(['city'])]
    private $context;

    #[ORM\Column(name: 'synonyms', type: 'json')]
    #[Groups(['city'])]
    #[OA\Property(type: 'array', items: new OA\Items(type: 'string'))]
    private $synonyms = [];

    #[ORM\Column(name: 'translations', type: 'json')]
    #[Groups(['city'])]
    #[OA\Property(properties: [
        new OA\Property(property: 'fr', type: 'string'),
        new OA\Property(property: 'it', type: 'string'),
    ], type: 'object')]
    private $translations = [];

    #[ORM\ManyToOne(targetEntity: 'State')]
    #[ORM\JoinColumn(name: 'state_id', referencedColumnName: 'id')]
    #[Groups(['city'])]
    private ?State $state = null;

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
     * Set isPublic
     *
     * @param boolean $isPublic
     *
     * @return City
     */
    public function setIsPublic($isPublic)
    {
        $this->isPublic = $isPublic;

        return $this;
    }

    /**
     * Get isPublic
     *
     * @return bool
     */
    public function getIsPublic()
    {
        return $this->isPublic;
    }

    /**
     * Set position
     *
     * @param int|null $position
     *
     * @return City
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
     * Set createdAt
     *
     * @param \DateTime $createdAt
     *
     * @return City
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
     * @return City
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
     * Set zipCode
     *
     * @param string $zipCode
     *
     * @return City
     */
    public function setZipCode($zipCode)
    {
        $this->zipCode = $zipCode;

        return $this;
    }

    /**
     * Get zipCode
     *
     * @return string
     */
    public function getZipCode()
    {
        return $this->zipCode;
    }

    /**
     * Set name
     *
     * @param string $name
     *
     * @return City
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set municipalNumber
     *
     * @param int|null $municipalNumber
     *
     * @return City
     */
    public function setMunicipalNumber($municipalNumber)
    {
        $this->municipalNumber = $municipalNumber;

        return $this;
    }

    /**
     * Get municipalNumber
     *
     * @return int|null
     */
    public function getMunicipalNumber()
    {
        return $this->municipalNumber;
    }

    /**
     * Set context
     *
     * @param string $context
     *
     * @return City
     */
    public function setContext($context)
    {
        $this->context = $context;

        return $this;
    }

    /**
     * Get context
     *
     * @return string
     */
    public function getContext()
    {
        return $this->context;
    }

    /**
     * Set synonyms
     *
     * @param array $synonyms
     *
     * @return City
     */
    public function setSynonyms($synonyms)
    {
        $this->synonyms = $synonyms;

        return $this;
    }

    /**
     * Get synonyms
     *
     * @return array
     */
    public function getSynonyms()
    {
        return $this->synonyms;
    }

    /**
     * Set translations
     *
     * @param array $translations
     *
     * @return City
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

    public function getState(): ?State
    {
        return $this->state;
    }

    public function setState(?State $state): self
    {
        $this->state = $state;

        return $this;
    }
}

