<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use OpenApi\Attributes as OA;

/**
 * InteractiveGraphic
 */
#[ORM\Table(name: 'pv_interactive_graphic')]
#[ORM\Entity(repositoryClass: 'App\Repository\InteractiveGraphicRepository')]
class InteractiveGraphic
{

    /**
     * @var int
     */
    #[ORM\Column(name: 'id', type: 'integer')]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    #[Groups(['id', 'interactive_graphic'])]
    private $id;

    /**
     * @var \DateTime
     */
    #[ORM\Column(name: 'created_at', type: 'datetime')]
    #[Groups(['interactive_graphic'])]
    private $createdAt;

    /**
     * @var \DateTime
     */
    #[ORM\Column(name: 'updated_at', type: 'datetime', nullable: true)]
    #[Groups(['interactive_graphic'])]
    private $updatedAt;

    /**
     * @var string
     */
    #[ORM\Column(name: 'title', type: 'text', nullable: true)]
    #[Groups(['interactive_graphic'])]
    private $title;

    /**
     * @var string
     */
    #[ORM\Column(name: 'description', type: 'text', nullable: true)]
    #[Groups(['interactive_graphic'])]
    private $description;

    /**
     * @var string
     */
    #[ORM\Column(name: 'svg', type: 'text', nullable: true)]
    #[Groups(['interactive_graphic'])]
    #[OA\Property(
        description: 'SVG code of the interactive graphic.',
        type: 'string'
    )]
    private $svg;

    /**
     * @var string
     */
    #[ORM\Column(name: 'selector', type: 'text', nullable: true)]
    #[Groups(['interactive_graphic'])]
    #[OA\Property(
        description: 'CSS selector to identify SVG elements that are meant to be interactive.',
        type: 'string'
    )]
    private $selector;

    /**
     * @var string
     */
    #[ORM\Column(name: 'start', type: 'text', nullable: true)]
    #[Groups(['interactive_graphic'])]
    #[OA\Property(
        description: 'Optional CSS selector to identify SVG elements that are meant to be "active" from the start.',
        type: 'string'
    )]
    private $start;

    /**
     * @var string
     */
    #[ORM\Column(name: 'copyright', type: 'text', nullable: true)]
    #[Groups(['interactive_graphic'])]
    private $copyright;

    /**
     * @var array
     */
    #[ORM\Column(name: 'config', type: 'json')]
    #[Groups(['interactive_graphic'])]
    #[OA\Property(
        description: 'Contains a hash table with keys for each interactive SVG element. The value is meant to be shown when interacting with the given element.',
        properties: [],
        type: 'object'
    )]
    private $config = [];

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
     * @return InteractiveGraphic
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
     * @return InteractiveGraphic
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
     * @return InteractiveGraphic
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
     * @return InteractiveGraphic
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
     * Set svg
     *
     * @param string $svg
     *
     * @return InteractiveGraphic
     */
    public function setSvg($svg)
    {
        $this->svg = $svg;

        return $this;
    }

    /**
     * Get svg
     *
     * @return string
     */
    public function getSvg()
    {
        return $this->svg;
    }

    /**
     * Set selector
     *
     * @param string $selector
     *
     * @return InteractiveGraphic
     */
    public function setSelector($selector)
    {
        $this->selector = $selector;

        return $this;
    }

    /**
     * Get selector
     *
     * @return string
     */
    public function getSelector()
    {
        return $this->selector;
    }

    /**
     * Set start
     *
     * @param string $start
     *
     * @return InteractiveGraphic
     */
    public function setStart($start)
    {
        $this->start = $start;

        return $this;
    }

    /**
     * Get start
     *
     * @return string
     */
    public function getStart()
    {
        return $this->start;
    }

    /**
     * Set copyright
     *
     * @param string $copyright
     *
     * @return InteractiveGraphic
     */
    public function setCopyright($copyright)
    {
        $this->copyright = $copyright;

        return $this;
    }

    /**
     * Get copyright
     *
     * @return string
     */
    public function getCopyright()
    {
        return $this->copyright;
    }

    /**
     * Set config
     *
     * @param array $config
     *
     * @return InteractiveGraphic
     */
    public function setConfig($config)
    {
        $this->config = $config;

        return $this;
    }

    /**
     * Get config
     *
     * @return array
     */
    public function getConfig()
    {
        return $this->config;
    }
}

