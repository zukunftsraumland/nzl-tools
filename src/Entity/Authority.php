<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use OpenApi\Attributes as OA;

/**
 * Authority
 */
#[ORM\Table(name: 'pv_authority')]
#[ORM\Entity(repositoryClass: 'App\Repository\AuthorityRepository')]
class Authority
{

    #[ORM\Column(name: 'id', type: 'integer')]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    #[Groups(['id', 'authority'])]
    private $id;

    #[ORM\Column(name: 'is_public', type: 'boolean')]
    #[Groups(['authority'])]
    private $isPublic;

    #[ORM\Column(name: 'position', type: 'integer', nullable: true)]
    #[Groups(['authority'])]
    private $position;

    #[ORM\Column(name: 'created_at', type: 'datetime')]
    #[Groups(['authority'])]
    private $createdAt;

    #[ORM\Column(name: 'updated_at', type: 'datetime', nullable: true)]
    #[Groups(['authority'])]
    private $updatedAt;

    #[ORM\Column(name: 'name', type: 'text', nullable: true)]
    #[Groups(['authority'])]
    private $name;

    #[ORM\Column(name: 'is_state_supported', type: 'boolean')]
    #[Groups(['authority'])]
    private $isStateSupported;

    #[ORM\Column(name: 'context', type: 'string', length: 255, nullable: true)]
    #[Groups(['authority'])]
    private $context;

    #[ORM\Column(name: 'synonyms', type: 'json')]
    #[Groups(['authority'])]
    #[OA\Property(type: 'array', items: new OA\Items(type: 'string'))]
    private $synonyms = [];

    #[ORM\Column(name: 'translations', type: 'json')]
    #[Groups(['authority'])]
    #[OA\Property(properties: [
        new OA\Property(property: 'fr', type: 'string'),
        new OA\Property(property: 'it', type: 'string'),
    ], type: 'object')]
    private $translations = [];

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
     * @return Authority
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
     * @return Authority
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
     * @return Authority
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
     * @return Authority
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
     * Set name
     *
     * @param string $name
     *
     * @return Authority
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
     * Set isStateSupported
     *
     * @param boolean $isStateSupported
     *
     * @return Authority
     */
    public function setIsStateSupported($isStateSupported)
    {
        $this->isStateSupported = $isStateSupported;

        return $this;
    }

    /**
     * Get isStateSupported
     *
     * @return bool
     */
    public function getIsStateSupported()
    {
        return $this->isStateSupported;
    }

    /**
     * Set context
     *
     * @param string $context
     *
     * @return Authority
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
     * @return Authority
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
     * @return Authority
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
}

