<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use OpenApi\Attributes as OA;

/**
 * ContactGroup
 */
#[ORM\Table(name: 'pv_contact_group')]
#[ORM\Entity(repositoryClass: 'App\Repository\ContactGroupRepository')]
class ContactGroup
{

    /**
     * @var int
     */
    #[ORM\Column(name: 'id', type: 'integer')]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    #[Groups(['id', 'contact_group'])]
    private $id;

    /**
     * @var bool
     */
    #[ORM\Column(name: 'is_public', type: 'boolean')]
    #[Groups(['contact_group'])]
    private $isPublic;

    /**
     * @var int|null
     */
    #[ORM\Column(name: 'position', type: 'integer', nullable: true)]
    #[Groups(['contact_group'])]
    private $position;

    /**
     * @var \DateTime
     */
    #[ORM\Column(name: 'created_at', type: 'datetime')]
    #[Groups(['contact_group'])]
    private $createdAt;

    /**
     * @var \DateTime
     */
    #[ORM\Column(name: 'updated_at', type: 'datetime', nullable: true)]
    #[Groups(['contact_group'])]
    private $updatedAt;

    /**
     * @var string
     */
    #[ORM\Column(name: 'name', type: 'text', nullable: true)]
    #[Groups(['contact_group'])]
    private $name;

    #[ORM\Column(name: 'tpoint_id', type: 'integer', nullable: true)]
    #[Groups(['contact_group'])]
    private $tpointId;

    #[ORM\Column(name: 'tpoint_checksum', type: 'string', length: 128, nullable: true)]
    #[Groups(['contact_group'])]
    private $tpointChecksum;

    /**
     * @var string
     */
    #[ORM\Column(name: 'context', type: 'string', length: 255, nullable: true)]
    #[Groups(['contact_group'])]
    private $context;

    /**
     * @var array
     */
    #[ORM\Column(name: 'synonyms', type: 'json')]
    #[Groups(['contact_group'])]
    #[OA\Property(type: 'array', items: new OA\Items(type: 'string'))]
    private $synonyms = [];

    /**
     * @var array
     */
    #[ORM\Column(name: 'translations', type: 'json')]
    #[Groups(['contact_group'])]
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
     * @return ContactGroup
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
     * @return ContactGroup
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
     * @return ContactGroup
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
     * @return ContactGroup
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
     * @return ContactGroup
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
     * Set tpointId
     *
     * @param int|null $tpointId
     *
     * @return self
     */
    public function setTpointId($tpointId)
    {
        $this->tpointId = $tpointId;

        return $this;
    }

    /**
     * Get tpointId
     *
     * @return int|null
     */
    public function getTpointId()
    {
        return $this->tpointId;
    }

    /**
     * Set tpointChecksum
     *
     * @param string $tpointChecksum
     *
     * @return self
     */
    public function setTpointChecksum($tpointChecksum)
    {
        $this->tpointChecksum = $tpointChecksum;

        return $this;
    }

    /**
     * Get tpointChecksum
     *
     * @return string
     */
    public function getTpointChecksum()
    {
        return $this->tpointChecksum;
    }

    /**
     * Set context
     *
     * @param string $context
     *
     * @return ContactGroup
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
     * @return ContactGroup
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
     * @return ContactGroup
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
