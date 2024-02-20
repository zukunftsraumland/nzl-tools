<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use OpenApi\Attributes as OA;

/**
 * Education
 */
#[ORM\Table(name: 'pv_education')]
#[ORM\Entity(repositoryClass: 'App\Repository\EducationRepository')]
class Education
{

    #[ORM\Column(name: 'id', type: 'integer')]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    #[Groups(['id', 'education'])]
    private $id;

    #[ORM\Column(name: 'is_public', type: 'boolean')]
    #[Groups(['education'])]
    private $isPublic;

    #[ORM\Column(name: 'created_at', type: 'datetime')]
    #[Groups(['education'])]
    private $createdAt;

    #[ORM\Column(name: 'updated_at', type: 'datetime', nullable: true)]
    #[Groups(['education'])]
    private $updatedAt;

    #[ORM\Column(name: 'name', type: 'text', nullable: true)]
    #[Groups(['education'])]
    private $name;

    #[ORM\Column(name: 'location', type: 'string', nullable: true)]
    #[Groups(['education'])]
    private $location;

    #[ORM\Column(name: 'organizer', type: 'string', nullable: true)]
    #[Groups(['education'])]
    private $organizer;

    #[ORM\Column(name: 'contact', type: 'text', nullable: true)]
    #[Groups(['education'])]
    private $contact;

    #[ORM\Column(name: 'description', type: 'text', nullable: true)]
    #[Groups(['education'])]
    private $description;

    #[ORM\Column(name: 'text', type: 'text', nullable: true)]
    #[Groups(['education'])]
    private $text;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    #[ORM\ManyToMany(targetEntity: 'EducationType')]
    #[ORM\JoinTable(name: 'pv_education_education_type')]
    #[ORM\JoinColumn(name: 'education_id', referencedColumnName: 'id')]
    #[ORM\InverseJoinColumn(name: 'education_type_id', referencedColumnName: 'id')]
    #[Groups(['education'])]
    private $educationTypes;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    #[ORM\ManyToMany(targetEntity: 'Language')]
    #[ORM\JoinTable(name: 'pv_education_language')]
    #[ORM\JoinColumn(name: 'education_id', referencedColumnName: 'id')]
    #[ORM\InverseJoinColumn(name: 'language_id', referencedColumnName: 'id')]
    #[Groups(['education'])]
    private $languages;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    #[ORM\ManyToMany(targetEntity: 'Location')]
    #[ORM\JoinTable(name: 'pv_education_location')]
    #[ORM\JoinColumn(name: 'education_id', referencedColumnName: 'id')]
    #[ORM\InverseJoinColumn(name: 'location_id', referencedColumnName: 'id')]
    #[Groups(['education'])]
    private $locations;

    #[ORM\Column(name: 'links', type: 'json')]
    #[Groups(['education'])]
    #[OA\Property(type: 'array', items: new OA\Items(
        properties: [
            new OA\Property(property: 'value', type: 'string'),
            new OA\Property(property: 'label', type: 'string'),
        ],
        type: 'object'
    ))]
    private $links = [];

    #[ORM\Column(name: 'videos', type: 'json')]
    #[Groups(['education'])]
    #[OA\Property(type: 'array', items: new OA\Items(
        properties: [
            new OA\Property(property: 'value', type: 'string'),
            new OA\Property(property: 'label', type: 'string'),
        ],
        type: 'object'
    ))]
    private $videos = [];

    #[ORM\Column(name: 'images', type: 'json')]
    #[Groups(['education'])]
    #[OA\Property(type: 'array', items: new OA\Items(
        properties: [
            new OA\Property(property: 'id', type: 'integer'),
            new OA\Property(property: 'name', type: 'string'),
            new OA\Property(property: 'extension', type: 'string'),
            new OA\Property(property: 'mimeType', type: 'string'),
            new OA\Property(property: 'copyright', type: 'string'),
            new OA\Property(property: 'description', type: 'string'),
        ],
        type: 'object'
    ))]
    private $images = [];

    #[ORM\Column(name: 'files', type: 'json')]
    #[Groups(['education'])]
    #[OA\Property(type: 'array', items: new OA\Items(
        properties: [
            new OA\Property(property: 'id', type: 'integer'),
            new OA\Property(property: 'name', type: 'string'),
            new OA\Property(property: 'extension', type: 'string'),
            new OA\Property(property: 'mimeType', type: 'string'),
            new OA\Property(property: 'description', type: 'string'),
        ],
        type: 'object'
    ))]
    private $files = [];

    #[ORM\Column(name: 'translations', type: 'json')]
    #[Groups(['education'])]
    #[OA\Property(properties: [
        new OA\Property(property: 'fr', type: 'object'),
        new OA\Property(property: 'it', type: 'object'),
    ], type: 'object')]
    private $translations = [];

    public function __construct()
    {
        $this->educationTypes = new ArrayCollection();
        $this->languages = new ArrayCollection();
        $this->locations = new ArrayCollection();
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
     * @return Education
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
     * Set createdAt
     *
     * @param \DateTime $createdAt
     *
     * @return Education
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
     * @return Education
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
     * @return Education
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
     * Set location
     *
     * @param string $location
     *
     * @return Education
     */
    public function setLocation($location)
    {
        $this->location = $location;

        return $this;
    }

    /**
     * Get location
     *
     * @return string
     */
    public function getLocation()
    {
        return $this->location;
    }

    /**
     * Set organizer
     *
     * @param string $organizer
     *
     * @return Education
     */
    public function setOrganizer($organizer)
    {
        $this->organizer = $organizer;

        return $this;
    }

    /**
     * Get organizer
     *
     * @return string
     */
    public function getOrganizer()
    {
        return $this->organizer;
    }

    /**
     * Set description
     *
     * @param string $description
     *
     * @return Education
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
     * Set contact
     *
     * @param string $contact
     *
     * @return Education
     */
    public function setContact($contact)
    {
        $this->contact = $contact;

        return $this;
    }

    /**
     * Get contact
     *
     * @return string
     */
    public function getContact()
    {
        return $this->contact;
    }

    /**
     * Set text
     *
     * @param string $text
     *
     * @return Education
     */
    public function setText($text)
    {
        $this->text = $text;

        return $this;
    }

    /**
     * Get text
     *
     * @return string
     */
    public function getText()
    {
        return $this->text;
    }

    /**
     * Get educationTypes
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getEducationTypes() {
        return $this->educationTypes;
    }

    /**
     * Set educationTypes
     *
     * @return Education
     */
    public function setEducationTypes($educationTypes) {
        $this->educationTypes = $educationTypes;

        return $this;
    }

    /**
     * Add to educationTypes
     *
     * @param $educationType
     * @return Education
     */
    public function addEducationType($educationType) {
        if (!$this->educationTypes->contains($educationType)) {
            $this->educationTypes->add($educationType);
        }

        return $this;
    }

    /**
     * Remove educationTypes
     *
     * @param $educationTypes
     */
    public function removeEducationType($educationTypes) {
        $this->educationTypes->removeElement($educationTypes);
    }

    /**
     * Get languages
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getLanguages() {
        return $this->languages;
    }

    /**
     * Set languages
     *
     * @return Education
     */
    public function setLanguages($languages) {
        $this->languages = $languages;

        return $this;
    }

    /**
     * Add to languages
     *
     * @param $language
     * @return Education
     */
    public function addLanguage($language) {
        if (!$this->languages->contains($language)) {
            $this->languages->add($language);
        }

        return $this;
    }

    /**
     * Remove languages
     *
     * @param $languages
     */
    public function removeLanguage($languages) {
        $this->languages->removeElement($languages);
    }

    /**
     * Get locations
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getLocations() {
        return $this->locations;
    }

    /**
     * Set locations
     *
     * @return Education
     */
    public function setLocations($locations) {
        $this->locations = $locations;

        return $this;
    }

    /**
     * Add to locations
     *
     * @param $location
     * @return Education
     */
    public function addLocation($location) {
        if (!$this->locations->contains($location)) {
            $this->locations->add($location);
        }

        return $this;
    }

    /**
     * Remove locations
     *
     * @param $locations
     */
    public function removeLocation($locations) {
        $this->locations->removeElement($locations);
    }

    /**
     * Set links
     *
     * @param array $links
     *
     * @return Education
     */
    public function setLinks($links)
    {
        $this->links = $links;

        return $this;
    }

    /**
     * Get links
     *
     * @return array
     */
    public function getLinks()
    {
        return $this->links;
    }

    /**
     * Set videos
     *
     * @param array $videos
     *
     * @return Education
     */
    public function setVideos($videos)
    {
        $this->videos = $videos;

        return $this;
    }

    /**
     * Get videos
     *
     * @return array
     */
    public function getVideos()
    {
        return $this->videos;
    }

    /**
     * Set images
     *
     * @param array $images
     *
     * @return Education
     */
    public function setImages($images)
    {
        $this->images = $images;

        return $this;
    }

    /**
     * Get images
     *
     * @return array
     */
    public function getImages()
    {
        return $this->images;
    }

    /**
     * Set files
     *
     * @param array $files
     *
     * @return Education
     */
    public function setFiles($files)
    {
        $this->files = $files;

        return $this;
    }

    /**
     * Get files
     *
     * @return array
     */
    public function getFiles()
    {
        return $this->files;
    }

    /**
     * Set translations
     *
     * @param array $translations
     *
     * @return Education
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

