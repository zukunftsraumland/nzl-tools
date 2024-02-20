<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use OpenApi\Attributes as OA;

/**
 * EventCollection
 */
#[ORM\Table(name: 'pv_event_collection')]
#[ORM\Entity(repositoryClass: 'App\Repository\EventCollectionRepository')]
class EventCollection
{

    #[ORM\Column(name: 'id', type: 'integer')]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    #[Groups(['id', 'event_collection'])]
    private $id;

    #[ORM\Column(name: 'is_dynamic', type: 'boolean')]
    #[Groups(['event_collection'])]
    private $isDynamic;

    #[ORM\Column(name: 'created_at', type: 'datetime')]
    #[Groups(['event_collection'])]
    private $createdAt;

    #[ORM\Column(name: 'updated_at', type: 'datetime', nullable: true)]
    #[Groups(['event_collection'])]
    private $updatedAt;

    #[ORM\Column(name: 'title', type: 'text', nullable: true)]
    #[Groups(['event_collection'])]
    private $title;

    #[ORM\Column(name: 'description', type: 'text', nullable: true)]
    #[Groups(['event_collection'])]
    private $description;

    #[ORM\Column(name: 'selection', type: 'json')]
    #[Groups(['event_collection'])]
    #[OA\Property(type: 'array', items: new OA\Items(
        properties: [
            new OA\Property(property: 'id', type: 'integer'),
        ],
        type: 'object'
    ))]
    private $selection = [];

    #[ORM\Column(name: 'filters', type: 'json')]
    #[Groups(['event_collection'])]
    #[OA\Property(type: 'array', items: new OA\Items(
        properties: [
            new OA\Property(property: 'type', type: 'string'),
            new OA\Property(property: 'value', type: 'string')
        ],
        type: 'object'
    ))]
    private $filters = [];

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
     * Set isDynamic
     *
     * @param boolean $isDynamic
     *
     * @return EventCollection
     */
    public function setIsDynamic($isDynamic)
    {
        $this->isDynamic = $isDynamic;

        return $this;
    }
    /**
     * Get isDynamic
     *
     * @return bool
     */
    public function getIsDynamic()
    {
        return $this->isDynamic;
    }
    /**
     * Set createdAt
     *
     * @param \DateTime $createdAt
     *
     * @return EventCollection
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
     * @return EventCollection
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
     * Set title
     *
     * @param string $title
     *
     * @return EventCollection
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
     * Set description
     *
     * @param string $description
     *
     * @return EventCollection
     */
    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }
    /**
     * Get description
     *
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }
    /**
     * Set selection
     *
     * @param array $selection
     *
     * @return EventCollection
     */
    public function setSelection($selection)
    {
        $this->selection = $selection;

        return $this;
    }
    /**
     * Get selection
     *
     * @return array
     */
    public function getSelection()
    {
        return $this->selection;
    }
    /**
     * Set filters
     *
     * @param array $filters
     *
     * @return EventCollection
     */
    public function setFilters($filters)
    {
        $this->filters = $filters;

        return $this;
    }
    /**
     * Get filters
     *
     * @return array
     */
    public function getFilters()
    {
        return $this->filters;
    }
}

