<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use OpenApi\Attributes as OA;

/**
 * Inbox
 */
#[ORM\Table(name: 'pv_inbox')]
#[ORM\Entity(repositoryClass: 'App\Repository\InboxRepository')]
class Inbox
{

    #[ORM\Column(name: 'id', type: 'integer')]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    #[Groups(['id', 'inbox'])]
    private $id;

    #[ORM\Column(name: 'created_at', type: 'datetime')]
    #[Groups(['inbox'])]
    private $createdAt;

    #[ORM\Column(name: 'updated_at', type: 'datetime', nullable: true)]
    #[Groups(['inbox'])]
    private $updatedAt;

    #[ORM\Column(name: 'source', type: 'string', length: 255)]
    #[Groups(['inbox'])]
    private $source;

    #[ORM\Column(name: 'foreign_id', type: 'string', nullable: true)]
    #[Groups(['inbox'])]
    private $foreignId;

    #[ORM\Column(name: 'type', type: 'string', length: 255)]
    #[Groups(['inbox'])]
    private $type;

    #[ORM\Column(name: 'internal_id', type: 'string', nullable: true)]
    #[Groups(['inbox'])]
    private $internalId;

    #[ORM\Column(name: 'title', type: 'text')]
    #[Groups(['inbox'])]
    private $title;

    #[ORM\Column(name: 'status', type: 'string', length: 255)]
    #[Groups(['inbox'])]
    private $status;

    #[ORM\Column(name: 'is_merged', type: 'boolean')]
    #[Groups(['inbox'])]
    private $isMerged;

    #[ORM\Column(name: 'merged_at', type: 'datetime', nullable: true)]
    #[Groups(['inbox'])]
    private $mergedAt;

    #[ORM\Column(name: 'data', type: 'json')]
    #[Groups(['inbox'])]
    #[OA\Property(properties: [], type: 'object')]
    private $data = [];

    #[ORM\Column(name: 'normalized_data', type: 'json')]
    #[Groups(['inbox'])]
    #[OA\Property(properties: [], type: 'object')]
    private $normalizedData = [];

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
     * @return Inbox
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
     * @return Inbox
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
     * Set source
     *
     * @param string $source
     *
     * @return Inbox
     */
    public function setSource($source)
    {
        $this->source = $source;

        return $this;
    }

    /**
     * Get source
     *
     * @return string
     */
    public function getSource()
    {
        return $this->source;
    }

    /**
     * Set foreignId
     *
     * @param string $foreignId
     *
     * @return Inbox
     */
    public function setForeignId($foreignId)
    {
        $this->foreignId = $foreignId;

        return $this;
    }

    /**
     * Get foreignId
     *
     * @return string
     */
    public function getForeignId()
    {
        return $this->foreignId;
    }

    /**
     * Set type
     *
     * @param string $type
     *
     * @return Inbox
     */
    public function setType($type)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * Get type
     *
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Set internalId
     *
     * @param string $internalId
     *
     * @return Inbox
     */
    public function setInternalId($internalId)
    {
        $this->internalId = $internalId;

        return $this;
    }

    /**
     * Get internalId
     *
     * @return string
     */
    public function getInternalId()
    {
        return $this->internalId;
    }

    /**
     * Set type
     *
     * @param string $title
     *
     * @return Inbox
     */
    public function setTitle($title)
    {
        $this->title = $title;

        return $this;
    }

    /**
     * Get title
     *
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Set status
     *
     * @param string $status
     *
     * @return Inbox
     */
    public function setStatus($status)
    {
        $this->status = $status;

        return $this;
    }

    /**
     * Get status
     *
     * @return string
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * Set isMerged
     *
     * @param boolean $isMerged
     *
     * @return Inbox
     */
    public function setIsMerged($isMerged)
    {
        $this->isMerged = $isMerged;

        return $this;
    }

    /**
     * Get isMerged
     *
     * @return bool
     */
    public function getIsMerged()
    {
        return $this->isMerged;
    }

    /**
     * Set mergedAt
     *
     * @param \DateTime $mergedAt
     *
     * @return Inbox
     */
    public function setMergedAt($mergedAt)
    {
        $this->mergedAt = $mergedAt;

        return $this;
    }

    /**
     * Get mergedAt
     *
     * @return \DateTime
     */
    public function getMergedAt()
    {
        return $this->mergedAt;
    }

    /**
     * Set data
     *
     * @param array $data
     *
     * @return Inbox
     */
    public function setData($data)
    {
        $this->data = $data;

        return $this;
    }

    /**
     * Get data
     *
     * @return array
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * Set normalizedData
     *
     * @param array $normalizedData
     *
     * @return Inbox
     */
    public function setNormalizedData($normalizedData)
    {
        $this->normalizedData = $normalizedData;

        return $this;
    }

    /**
     * Get normalizedData
     *
     * @return array
     */
    public function getNormalizedData()
    {
        return $this->normalizedData;
    }
}

