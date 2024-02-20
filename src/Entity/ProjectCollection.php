<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use OpenApi\Attributes as OA;

/**
 * ProjectCollection
 */
#[ORM\Table(name: 'pv_project_collection')]
#[ORM\Entity(repositoryClass: 'App\Repository\ProjectCollectionRepository')]
class ProjectCollection
{

    #[ORM\Column(name: 'id', type: 'integer')]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    #[Groups(['id', 'project_collection'])]
    private $id;

    #[ORM\Column(name: 'is_dynamic', type: 'boolean')]
    #[Groups(['project_collection'])]
    private $isDynamic;

    #[ORM\Column(name: 'created_at', type: 'datetime')]
    #[Groups(['project_collection'])]
    private $createdAt;

    #[ORM\Column(name: 'updated_at', type: 'datetime', nullable: true)]
    #[Groups(['project_collection'])]
    private $updatedAt;

    #[ORM\Column(name: 'title', type: 'text', nullable: true)]
    #[Groups(['project_collection'])]
    private $title;

    #[ORM\Column(name: 'description', type: 'text', nullable: true)]
    #[Groups(['project_collection'])]
    private $description;

    #[ORM\Column(name: 'selection', type: 'json')]
    #[Groups(['project_collection'])]
    #[OA\Property(type: 'array', items: new OA\Items(
        properties: [
            new OA\Property(property: 'id', type: 'integer'),
        ],
        type: 'object'
    ))]
    private $selection = [];

    #[ORM\Column(name: 'filters', type: 'json')]
    #[Groups(['project_collection'])]
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
     * @return ProjectCollection
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
     * @return ProjectCollection
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
     * @return ProjectCollection
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
     * @return ProjectCollection
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
     * @return ProjectCollection
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
     * @return ProjectCollection
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
     * @return ProjectCollection
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

