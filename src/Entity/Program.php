<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use OpenApi\Attributes as OA;

/**
 * Program
 */
#[ORM\Table(name: 'pv_program')]
#[ORM\Entity(repositoryClass: 'App\Repository\ProgramRepository')]
class Program
{

    #[ORM\Column(name: 'id', type: 'integer')]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    #[Groups(['id', 'program'])]
    private $id;

    #[ORM\Column(name: 'is_public', type: 'boolean')]
    #[Groups(['program'])]
    private $isPublic;

    #[ORM\Column(name: 'position', type: 'integer', nullable: true)]
    #[Groups(['program'])]
    private $position;

    #[ORM\Column(name: 'created_at', type: 'datetime')]
    #[Groups(['program'])]
    private $createdAt;

    #[ORM\Column(name: 'updated_at', type: 'datetime', nullable: true)]
    #[Groups(['program'])]
    private $updatedAt;

    #[ORM\Column(name: 'name', type: 'text', nullable: true)]
    #[Groups(['program'])]
    private $name;

    #[ORM\Column(name: 'long_name', type: 'text', nullable: true)]
    #[Groups(['program'])]
    private $longName;

    #[ORM\Column(name: 'url', type: 'text', nullable: true)]
    #[Groups(['program'])]
    private $url;

    #[ORM\Column(name: 'synonyms', type: 'json')]
    #[Groups(['program'])]
    #[OA\Property(type: 'array', items: new OA\Items(type: 'string'))]
    private $synonyms = [];

    #[ORM\Column(name: 'translations', type: 'json')]
    #[Groups(['program'])]
    #[OA\Property(properties: [
        new OA\Property(property: 'fr', properties: [
            new OA\Property(property: 'name', type: 'string'),
            new OA\Property(property: 'longName', type: 'string'),
            new OA\Property(property: 'url', type: 'string'),
        ], type: 'object'),
        new OA\Property(property: 'it', properties: [
            new OA\Property(property: 'name', type: 'string'),
            new OA\Property(property: 'longName', type: 'string'),
            new OA\Property(property: 'url', type: 'string'),
        ], type: 'object')
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
     * @return Program
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
     * @return Program
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
     * @return Program
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
     * @return Program
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
     * @return Program
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
     * Set longName
     *
     * @param string $longName
     *
     * @return Program
     */
    public function setLongName($longName)
    {
        $this->longName = $longName;

        return $this;
    }

    /**
     * Get longName
     *
     * @return string
     */
    public function getLongName()
    {
        return $this->longName;
    }

    /**
     * Set url
     *
     * @param string $url
     *
     * @return Program
     */
    public function setUrl($url)
    {
        $this->url = $url;

        return $this;
    }

    /**
     * Get url
     *
     * @return string
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * Set synonyms
     *
     * @param array $synonyms
     *
     * @return Program
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
     * @return Program
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

