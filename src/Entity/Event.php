<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use OpenApi\Attributes as OA;

/**
 * Event
 */
#[ORM\Table(name: 'pv_event')]
#[ORM\Entity(repositoryClass: 'App\Repository\EventRepository')]
class Event
{

    #[ORM\Column(name: 'id', type: 'integer')]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    #[Groups(['id', 'event'])]
    private $id;

    #[ORM\Column(name: 'is_public', type: 'boolean')]
    #[Groups(['event'])]
    private $isPublic;

    #[ORM\Column(name: 'is_promoted_de', type: 'boolean')]
    #[Groups(['event'])]
    private $isPromotedDE;

    #[ORM\Column(name: 'is_promoted_fr', type: 'boolean')]
    #[Groups(['event'])]
    private $isPromotedFR;

    #[ORM\Column(name: 'is_promoted_it', type: 'boolean')]
    #[Groups(['event'])]
    private $isPromotedIT;

    #[ORM\Column(name: 'created_at', type: 'datetime')]
    #[Groups(['event'])]
    private $createdAt;

    #[ORM\Column(name: 'updated_at', type: 'datetime', nullable: true)]
    #[Groups(['event'])]
    private $updatedAt;

    #[ORM\Column(name: 'title', type: 'text', nullable: true)]
    #[Groups(['event'])]
    private $title;

    #[ORM\Column(name: 'type', type: 'string', nullable: true)]
    #[Groups(['event'])]
    private $type;

    #[ORM\Column(name: 'color', type: 'string', nullable: true)]
    #[Groups(['event'])]
    private $color;

    #[ORM\Column(name: 'location', type: 'string', nullable: true)]
    #[Groups(['event'])]
    private $location;

    #[ORM\Column(name: 'organizer', type: 'string', nullable: true)]
    #[Groups(['event'])]
    private $organizer;

    #[ORM\Column(name: 'contact', type: 'text', nullable: true)]
    #[Groups(['event'])]
    private $contact;

    #[ORM\Column(name: 'description', type: 'text', nullable: true)]
    #[Groups(['event'])]
    private $description;

    #[ORM\Column(name: 'text', type: 'text', nullable: true)]
    #[Groups(['event'])]
    private $text;

    #[ORM\Column(name: 'registration', type: 'text', nullable: true)]
    #[Groups(['event'])]
    private $registration;

    #[ORM\Column(name: 'start_date', type: 'datetime', nullable: true)]
    #[Groups(['event'])]
    private $startDate;

    #[ORM\Column(name: 'end_date', type: 'datetime', nullable: true)]
    #[Groups(['event'])]
    private $endDate;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    #[ORM\ManyToMany(targetEntity: 'Topic')]
    #[ORM\JoinTable(name: 'pv_event_topic')]
    #[ORM\JoinColumn(name: 'event_id', referencedColumnName: 'id')]
    #[ORM\InverseJoinColumn(name: 'topic_id', referencedColumnName: 'id')]
    #[Groups(['event'])]
    private $topics;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    #[ORM\ManyToMany(targetEntity: 'Language')]
    #[ORM\JoinTable(name: 'pv_event_language')]
    #[ORM\JoinColumn(name: 'event_id', referencedColumnName: 'id')]
    #[ORM\InverseJoinColumn(name: 'language_id', referencedColumnName: 'id')]
    #[Groups(['event'])]
    private $languages;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    #[ORM\ManyToMany(targetEntity: 'Location')]
    #[ORM\JoinTable(name: 'pv_event_location')]
    #[ORM\JoinColumn(name: 'event_id', referencedColumnName: 'id')]
    #[ORM\InverseJoinColumn(name: 'location_id', referencedColumnName: 'id')]
    #[Groups(['event'])]
    private $locations;

    #[ORM\Column(name: 'programs', type: 'json')]
    #[Groups(['event'])]
    #[OA\Property(type: 'array', items: new OA\Items(
        properties: [
            new OA\Property(property: 'title', type: 'string'),
            new OA\Property(property: 'units', type: 'array', items: new OA\Items(
                properties: [
                    new OA\Property(property: 'time', type: 'string'),
                    new OA\Property(property: 'descriptions', type: 'array', items: new OA\Items(
                        properties: [
                            new OA\Property(property: 'value', type: 'string'),
                        ],
                        type: 'object'
                    )),
                    new OA\Property(
                        property: 'attachments',
                        type: 'array',
                        items: new OA\Items(
                            properties: [
                                new OA\Property(property: 'id', type: 'integer'),
                                new OA\Property(property: 'name', type: 'string'),
                                new OA\Property(property: 'extension', type: 'string'),
                                new OA\Property(property: 'mimeType', type: 'string'),
                            ],
                            type: 'object'
                        )
                    ),
                    new OA\Property(
                        property: 'translations',
                        properties: [
                            new OA\Property(property: 'fr', type: 'object'),
                            new OA\Property(property: 'it', type: 'object'),
                        ],
                        type: 'object'
                    ),
                ],
                type: 'object'
            )),
        ],
        type: 'object'
    ))]
    private $programs = [];

    #[ORM\Column(name: 'links', type: 'json')]
    #[Groups(['event'])]
    #[OA\Property(type: 'array', items: new OA\Items(
        properties: [
            new OA\Property(property: 'value', type: 'string'),
            new OA\Property(property: 'label', type: 'string'),
        ],
        type: 'object'
    ))]
    private $links = [];

    #[ORM\Column(name: 'videos', type: 'json')]
    #[Groups(['event'])]
    #[OA\Property(type: 'array', items: new OA\Items(
        properties: [
            new OA\Property(property: 'value', type: 'string'),
            new OA\Property(property: 'label', type: 'string'),
        ],
        type: 'object'
    ))]
    private $videos = [];

    #[ORM\Column(name: 'images', type: 'json')]
    #[Groups(['event'])]
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
    #[Groups(['event'])]
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
    #[Groups(['event'])]
    #[OA\Property(properties: [
        new OA\Property(property: 'fr', type: 'object'),
        new OA\Property(property: 'it', type: 'object'),
    ], type: 'object')]
    private $translations = [];

    public function __construct()
    {
        $this->topics = new ArrayCollection();
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
     * @return Event
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
     * Set isPromotedDE
     *
     * @param boolean $isPromotedDE
     *
     * @return Event
     */
    public function setIsPromotedDE($isPromotedDE)
    {
        $this->isPromotedDE = $isPromotedDE;

        return $this;
    }

    /**
     * Get isPromotedDE
     *
     * @return bool
     */
    public function getIsPromotedDE()
    {
        return $this->isPromotedDE;
    }

    /**
     * Set isPromotedFR
     *
     * @param boolean $isPromotedFR
     *
     * @return Event
     */
    public function setIsPromotedFR($isPromotedFR)
    {
        $this->isPromotedFR = $isPromotedFR;

        return $this;
    }

    /**
     * Get isPromotedFR
     *
     * @return bool
     */
    public function getIsPromotedFR()
    {
        return $this->isPromotedFR;
    }

    /**
     * Set isPromotedIT
     *
     * @param boolean $isPromotedIT
     *
     * @return Event
     */
    public function setIsPromotedIT($isPromotedIT)
    {
        $this->isPromotedIT = $isPromotedIT;

        return $this;
    }

    /**
     * Get isPromotedIT
     *
     * @return bool
     */
    public function getIsPromotedIT()
    {
        return $this->isPromotedIT;
    }

    /**
     * Set createdAt
     *
     * @param \DateTime $createdAt
     *
     * @return Event
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
     * @return Event
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
     * @return Event
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
     * Set type
     *
     * @param string $type
     *
     * @return Event
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
     * Set color
     *
     * @param string $color
     *
     * @return Event
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
     * Set location
     *
     * @param string $location
     *
     * @return Event
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
     * @return Event
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
     * @return Event
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
     * @return Event
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
     * @return Event
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
     * Set registration
     *
     * @param string $registration
     *
     * @return Event
     */
    public function setRegistration($registration)
    {
        $this->registration = $registration;

        return $this;
    }

    /**
     * Get registration
     *
     * @return string
     */
    public function getRegistration()
    {
        return $this->registration;
    }

    /**
     * Set startDate
     *
     * @param \DateTime $startDate
     *
     * @return Event
     */
    public function setStartDate($startDate)
    {
        $this->startDate = $startDate;

        return $this;
    }

    /**
     * Get startDate
     *
     * @return \DateTime
     */
    public function getStartDate()
    {
        return $this->startDate;
    }

    /**
     * Set endDate
     *
     * @param \DateTime $endDate
     *
     * @return Event
     */
    public function setEndDate($endDate)
    {
        $this->endDate = $endDate;

        return $this;
    }

    /**
     * Get endDate
     *
     * @return \DateTime
     */
    public function getEndDate()
    {
        return $this->endDate;
    }

    /**
     * Get topics
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getTopics() {
        return $this->topics;
    }

    /**
     * Set topics
     *
     * @return Event
     */
    public function setTopics($topics) {
        $this->topics = $topics;

        return $this;
    }

    /**
     * Add to topics
     *
     * @param $topic
     * @return Event
     */
    public function addTopic($topic) {
        if (!$this->topics->contains($topic)) {
            $this->topics->add($topic);
        }

        return $this;
    }

    /**
     * Remove topics
     *
     * @param $topics
     */
    public function removeTopic($topics) {
        $this->topics->removeElement($topics);
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
     * @return Event
     */
    public function setLanguages($languages) {
        $this->languages = $languages;

        return $this;
    }

    /**
     * Add to languages
     *
     * @param $language
     * @return Event
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
     * @return Event
     */
    public function setLocations($locations) {
        $this->locations = $locations;

        return $this;
    }

    /**
     * Add to locations
     *
     * @param $location
     * @return Event
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
     * @return Event
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
     * @return Event
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
     * Set programs
     *
     * @param array $programs
     *
     * @return Event
     */
    public function setPrograms($programs)
    {
        $this->programs = $programs;

        return $this;
    }

    /**
     * Get programs
     *
     * @return array
     */
    public function getPrograms()
    {
        return $this->programs;
    }

    /**
     * Set images
     *
     * @param array $images
     *
     * @return Event
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
     * @return Event
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
     * @return Event
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

