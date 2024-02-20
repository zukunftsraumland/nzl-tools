<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use OpenApi\Attributes as OA;

/**
 * Job
 */
#[ORM\Table(name: 'pv_job')]
#[ORM\Entity(repositoryClass: 'App\Repository\JobRepository')]
class Job
{

    #[ORM\Column(name: 'id', type: 'integer')]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    #[Groups(['id', 'job'])]
    private $id;

    #[ORM\Column(name: 'is_public', type: 'boolean')]
    #[Groups(['job'])]
    private $isPublic;

    #[ORM\Column(name: 'created_at', type: 'datetime')]
    #[Groups(['job'])]
    private $createdAt;

    #[ORM\Column(name: 'updated_at', type: 'datetime', nullable: true)]
    #[Groups(['job'])]
    private $updatedAt;

    #[ORM\Column(name: 'position', type: 'integer')]
    #[Groups(['job'])]
    private $position;

    #[ORM\Column(name: 'name', type: 'text', nullable: true)]
    #[Groups(['job'])]
    private $name;

    #[ORM\Column(name: 'location', type: 'string', nullable: true)]
    #[Groups(['job'])]
    private $location;

    #[ORM\Column(name: 'employer', type: 'string', nullable: true)]
    #[Groups(['job'])]
    private $employer;

    #[ORM\Column(name: 'contact', type: 'text', nullable: true)]
    #[Groups(['job'])]
    private $contact;

    #[ORM\Column(name: 'description', type: 'text', nullable: true)]
    #[Groups(['job'])]
    private $description;

    #[ORM\Column(name: 'application_deadline', type: 'datetime', nullable: true)]
    #[Groups(['job'])]
    private $applicationDeadline;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    #[ORM\ManyToMany(targetEntity: 'Location')]
    #[ORM\JoinTable(name: 'pv_job_location')]
    #[ORM\JoinColumn(name: 'job_id', referencedColumnName: 'id')]
    #[ORM\InverseJoinColumn(name: 'location_id', referencedColumnName: 'id')]
    #[Groups(['job'])]
    private $locations;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    #[ORM\ManyToMany(targetEntity: 'Stint')]
    #[ORM\JoinTable(name: 'pv_job_stint')]
    #[ORM\JoinColumn(name: 'job_id', referencedColumnName: 'id')]
    #[ORM\InverseJoinColumn(name: 'stint_id', referencedColumnName: 'id')]
    #[Groups(['job'])]
    private $stints;

    #[ORM\Column(name: 'links', type: 'json')]
    #[Groups(['job'])]
    #[OA\Property(type: 'array', items: new OA\Items(
        properties: [
            new OA\Property(property: 'value', type: 'string'),
            new OA\Property(property: 'label', type: 'string'),
        ],
        type: 'object'
    ))]
    private $links = [];

    #[ORM\Column(name: 'files', type: 'json')]
    #[Groups(['job'])]
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
    #[Groups(['job'])]
    #[OA\Property(properties: [
        new OA\Property(property: 'fr', type: 'object'),
        new OA\Property(property: 'it', type: 'object'),
    ], type: 'object')]
    private $translations = [];

    public function __construct()
    {
        $this->locations = new ArrayCollection();
        $this->stints = new ArrayCollection();
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
     * @return Job
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
     * @param boolean $position
     *
     * @return Job
     */
    public function setPosition($position)
    {
        $this->position = $position;

        return $this;
    }

    /**
     * Get position
     *
     * @return bool
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
     * @return Job
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
     * @return Job
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
     * @return Job
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
     * @return Job
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
     * Set employer
     *
     * @param string $employer
     *
     * @return Job
     */
    public function setEmployer($employer)
    {
        $this->employer = $employer;

        return $this;
    }

    /**
     * Get employer
     *
     * @return string
     */
    public function getEmployer()
    {
        return $this->employer;
    }

    /**
     * Set description
     *
     * @param string $description
     *
     * @return Job
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
     * @return Job
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
     * Set applicationDeadline
     *
     * @param \DateTime $applicationDeadline
     *
     * @return Job
     */
    public function setApplicationDeadline($applicationDeadline)
    {
        $this->applicationDeadline = $applicationDeadline;

        return $this;
    }

    /**
     * Get applicationDeadline
     *
     * @return \DateTime
     */
    public function getApplicationDeadline()
    {
        return $this->applicationDeadline;
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
     * @return Job
     */
    public function setLocations($locations) {
        $this->locations = $locations;

        return $this;
    }

    /**
     * Add to locations
     *
     * @param $location
     * @return Job
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
     * Get stints
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getStints() {
        return $this->stints;
    }

    /**
     * Set stints
     *
     * @return Job
     */
    public function setStints($stints) {
        $this->stints = $stints;

        return $this;
    }

    /**
     * Add to stints
     *
     * @param $stint
     * @return Job
     */
    public function addStint($stint) {
        if (!$this->stints->contains($stint)) {
            $this->stints->add($stint);
        }

        return $this;
    }

    /**
     * Remove stints
     *
     * @param $stints
     */
    public function removeStint($stints) {
        $this->stints->removeElement($stints);
    }

    /**
     * Set links
     *
     * @param array $links
     *
     * @return Job
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
     * Set files
     *
     * @param array $files
     *
     * @return Job
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
     * @return Job
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