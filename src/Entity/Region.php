<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use OpenApi\Attributes as OA;

/**
 * Region
 */
#[ORM\Table(name: 'pv_region')]
#[ORM\Entity(repositoryClass: 'App\Repository\RegionRepository')]
class Region
{

    #[ORM\Column(name: 'id', type: 'integer')]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    #[Groups(['id', 'region'])]
    private $id;

    #[ORM\Column(name: 'is_public', type: 'boolean')]
    #[Groups(['region'])]
    private $isPublic;

    #[ORM\Column(name: 'position', type: 'integer', nullable: true)]
    #[Groups(['region'])]
    private $position;

    #[ORM\Column(name: 'created_at', type: 'datetime')]
    #[Groups(['region'])]
    private $createdAt;

    #[ORM\Column(name: 'updated_at', type: 'datetime', nullable: true)]
    #[Groups(['region'])]
    private $updatedAt;

    #[ORM\Column(name: 'type', type: 'string', length: 64, nullable: true)]
    #[Groups(['region'])]
    private $type;

    #[ORM\Column(name: 'name', type: 'text', nullable: true)]
    #[Groups(['region'])]
    private $name;

    #[ORM\Column(name: 'url', type: 'string', length: 255, nullable: true)]
    #[Groups(['region'])]
    private $url;

    #[ORM\Column(name: 'color', type: 'string', length: 16, nullable: true)]
    #[Groups(['region'])]
    private $color;

    #[ORM\Column(name: 'description', type: 'text', nullable: true)]
    #[Groups(['region'])]
    private $description;

    #[ORM\Column(name: 'context', type: 'string', length: 255, nullable: true)]
    #[Groups(['region'])]
    private $context;

    #[ORM\Column(name: 'synonyms', type: 'json')]
    #[Groups(['region'])]
    #[OA\Property(type: 'array', items: new OA\Items(type: 'string'))]
    private $synonyms = [];

    #[ORM\Column(name: 'translations', type: 'json')]
    #[Groups(['region'])]
    #[OA\Property(properties: [
        new OA\Property(property: 'fr', type: 'string'),
        new OA\Property(property: 'it', type: 'string'),
    ], type: 'object')]
    private $translations = [];

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    #[ORM\ManyToMany(targetEntity: 'City')]
    #[ORM\JoinTable(name: 'pv_region_city')]
    #[ORM\JoinColumn(name: 'region_id', referencedColumnName: 'id')]
    #[ORM\InverseJoinColumn(name: 'city_id', referencedColumnName: 'id')]
    #[Groups(['region'])]
    private $cities;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    #[ORM\ManyToMany(targetEntity: 'Contact')]
    #[ORM\JoinTable(name: 'pv_region_contact')]
    #[ORM\JoinColumn(name: 'region_id', referencedColumnName: 'id')]
    #[ORM\InverseJoinColumn(name: 'contact_id', referencedColumnName: 'id')]
    #[Groups(['region'])]
    private $contacts;

    public function __construct()
    {
        $this->cities = new ArrayCollection();
        $this->contacts = new ArrayCollection();
    }

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
     * @return Region
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
     * @return Region
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
     * @return Region
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
     * @return Region
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
     * Set type
     *
     * @param string $type
     *
     * @return Region
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
     * Set name
     *
     * @param string $name
     *
     * @return Region
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
     * Set context
     *
     * @param string $context
     *
     * @return Region
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
     * Set url
     *
     * @param string $url
     *
     * @return Region
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
     * Set color
     *
     * @param string $color
     *
     * @return Region
     */
    public function setColor($color)
    {
        $this->color = $color;

        return $this;
    }

    /**
     * Get color
     *
     * @return string
     */
    public function getColor()
    {
        return $this->color;
    }

    /**
     * Set description
     *
     * @param string $description
     *
     * @return Region
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
     * Set synonyms
     *
     * @param array $synonyms
     *
     * @return Region
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
     * @return Region
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

    /**
     * Get cities
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getCities() {
        return $this->cities;
    }

    /**
     * Set cities
     *
     * @return self
     */
    public function setCities($cities) {
        $this->cities = $cities;

        return $this;
    }

    /**
     * Add to cities
     *
     * @param $city
     * @return self
     */
    public function addCity($city) {
        if (!$this->cities->contains($city)) {
            $this->cities->add($city);
        }

        return $this;
    }

    /**
     * Remove cities
     *
     * @param $cities
     */
    public function removeCity($cities) {
        $this->cities->removeElement($cities);
    }

    /**
     * Get contacts
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getContacts() {
        return $this->contacts;
    }

    /**
     * Set contacts
     *
     * @return self
     */
    public function setContacts($contacts) {
        $this->contacts = $contacts;

        return $this;
    }

    /**
     * Add to contacts
     *
     * @param $contact
     * @return self
     */
    public function addContact($contact) {
        if (!$this->contacts->contains($contact)) {
            $this->contacts->add($contact);
        }

        return $this;
    }

    /**
     * Remove contacts
     *
     * @param $contacts
     */
    public function removeContact($contacts) {
        $this->contacts->removeElement($contacts);
    }
}

