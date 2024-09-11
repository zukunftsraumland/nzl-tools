<?php

namespace App\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use OpenApi\Attributes as OA;

/**
 * Project
 */
#[ORM\Table(name: 'pv_project')]
#[ORM\Entity(repositoryClass: 'App\Repository\ProjectRepository')]
class Project
{

    #[ORM\Column(name: 'id', type: 'integer')]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    #[Groups(['id', 'project'])]
    private $id;

    #[ORM\Column(name: 'is_public', type: 'boolean')]
    #[Groups(['project'])]
    private $isPublic;

    #[ORM\Column(name: 'created_at', type: 'datetime')]
    #[Groups(['project'])]
    private $createdAt;

    #[ORM\Column(name: 'updated_at', type: 'datetime', nullable: true)]
    #[Groups(['project'])]
    private $updatedAt;

    #[ORM\Column(name: 'project_code', type: 'string', length: 255, nullable: true)]
    #[Groups(['project'])]
    private $projectCode;

    #[ORM\Column(name: 'source', type: 'string', length: 255, nullable: true)]
    #[Groups(['project'])]
    private $source;

    #[ORM\Column(name: 'foreign_id', type: 'string', length: 255, nullable: true)]
    #[Groups(['project'])]
    private $foreignId;

    #[ORM\Column(name: 'random', type: 'integer', nullable: true)]
    #[Groups(['project'])]
    private $random;

    #[ORM\Column(name: 'title', type: 'text', nullable: true)]
    #[Groups(['project'])]
    private $title;

    #[ORM\Column(name: 'keywords', type: 'text', nullable: true)]
    #[Groups(['project'])]
    private $keywords;

    #[ORM\Column(name: 'description', type: 'text', nullable: true)]
    #[Groups(['project'])]
    private $description;

    #[ORM\Column(name: 'lat', type: 'string', length: 255, nullable: true)]
    #[Groups(['project'])]
    private $lat;

    #[ORM\Column(name: 'lng', type: 'string', length: 255, nullable: true)]
    #[Groups(['project'])]
    private $lng;

    #[ORM\ManyToOne(targetEntity: 'App\Entity\LocalWorkgroup')]
    #[ORM\JoinColumn(name: 'local_workgroup_id', referencedColumnName: 'id', nullable: true)]
    #[Groups(['project'])]
    private $localWorkgroup;

    #[ORM\ManyToMany(targetEntity: 'App\Entity\LocalWorkgroup')]
    #[ORM\JoinTable(name: 'pv_project_local_workgroups')]
    #[Groups(['project'])]
    private Collection $localWorkgroups;

    #[ORM\Column(name: 'cooperation_project_at', type: 'boolean', nullable: true)]
    #[Groups(['project'])]
    private $cooperationProjectAt = false;

    #[ORM\Column(name: 'cooperation_project_eu', type: 'boolean', nullable: true)]
    #[Groups(['project'])]
    private $cooperationProjectEu = false;

    #[ORM\Column(name: 'synergy', type: 'boolean', nullable: true)]
    #[Groups(['project'])]
    private $synergy = false;

    #[ORM\Column(name: 'synergyGoal', type: 'boolean', nullable: true)]
    #[Groups(['project'])]
    private $synergyGoal = false;

    #[ORM\Column(name: 'case_study', type: 'boolean', nullable: true)]
    #[Groups(['project'])]
    private $caseStudy = false;

    #[ORM\Column(name: 'exemplary', type: Types::TEXT, nullable: true)]
    #[Groups(['project'])]
    private ?string $exemplary = null;

    #[ORM\Column(name: 'initial_context', type: Types::TEXT, nullable: true)]
    #[Groups(['project'])]
    private ?string $initialContext = null;

    #[ORM\Column(name: 'initial_context_goals', type: Types::TEXT, nullable: true)]
    #[Groups(['project'])]
    private ?string $initialContextGoals = null;

    #[ORM\Column(name: 'additional_value', type: Types::TEXT, nullable: true)]
    #[Groups(['project'])]
    private ?string $additionalValue = null;

    #[ORM\Column(name: 'additional_value_result', type: Types::TEXT, nullable: true)]
    #[Groups(['project'])]
    private ?string $additionalValueResult = null;

    #[ORM\Column(name: 'innovations', type: Types::TEXT, nullable: true)]
    #[Groups(['project'])]
    private ?string $innovations = null;

    #[ORM\Column(name: 'integration_young_citizen', type: Types::TEXT, nullable: true)]
    #[Groups(['project'])]
    private ?string $integrationYoungCitizen = null;

    #[ORM\Column(name: 'integration_female_citizen', type: Types::TEXT, nullable: true)]
    #[Groups(['project'])]
    private ?string $integrationFemaleCitizen = null;

    #[ORM\Column(name: 'integration_minorities', type: Types::TEXT, nullable: true)]
    #[Groups(['project'])]
    private ?string $integrationMinorities = null;

    #[ORM\Column(name: 'learning_experience', type: Types::TEXT, nullable: true)]
    #[Groups(['project'])]
    private ?string $learningExperience = null;

    #[ORM\Column(name: 'transferable', type: Types::TEXT, nullable: true)]
    #[Groups(['project'])]
    private ?string $transferable = null;

    #[ORM\Column(name: 'transfer_details', type: Types::TEXT, nullable: true)]
    #[Groups(['project'])]
    private ?string $transferDetails = null;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    #[ORM\ManyToMany(targetEntity: 'Topic')]
    #[ORM\JoinTable(name: 'pv_project_topic')]
    #[ORM\JoinColumn(name: 'project_id', referencedColumnName: 'id')]
    #[ORM\InverseJoinColumn(name: 'topic_id', referencedColumnName: 'id')]
    #[Groups(['project'])]
    private $topics;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    #[ORM\ManyToMany(targetEntity: 'Tag')]
    #[ORM\JoinTable(name: 'pv_project_tag')]
    #[ORM\JoinColumn(name: 'project_id', referencedColumnName: 'id')]
    #[ORM\InverseJoinColumn(name: 'tag_id', referencedColumnName: 'id')]
    #[Groups(['project'])]
    private $tags;

    #[ORM\ManyToOne(targetEntity: LEPeriod::class)]
    #[ORM\JoinColumn(nullable: true)]
    #[Groups(['project'])]
    private ?LEPeriod $lePeriod;
    
    #[ORM\ManyToOne(targetEntity: LEFundingCategory::class)]
    #[ORM\JoinColumn(nullable: true)]
    #[Groups(['project'])]
    private ?LEFundingCategory $leFundingCategory;
    
    #[ORM\ManyToOne(targetEntity: LEFundingArticle::class)]
    #[ORM\JoinColumn(nullable: true)]
    #[Groups(['project'])]
    private ?LEFundingArticle $leFundingArticle;
    
    #[ORM\ManyToOne(targetEntity: LEFundingMethod::class)]
    #[ORM\JoinColumn(nullable: true)]
    #[Groups(['project'])]
    private ?LEFundingMethod $leFundingMethod;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    #[ORM\ManyToMany(targetEntity: 'Tag')]
    #[ORM\JoinTable(name: 'pv_project_synergy_fund_tags')]
    #[ORM\JoinColumn(name: 'project_id', referencedColumnName: 'id')]
    #[ORM\InverseJoinColumn(name: 'tag_id', referencedColumnName: 'id')]
    #[Groups(['project'])]
    private $synergyFundTags;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    #[ORM\ManyToMany(targetEntity: 'Tag')]
    #[ORM\JoinTable(name: 'pv_project_synergy_goal_tags')]
    #[ORM\JoinColumn(name: 'project_id', referencedColumnName: 'id')]
    #[ORM\InverseJoinColumn(name: 'tag_id', referencedColumnName: 'id')]
    #[Groups(['project'])]
    private $synergyGoalTags;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    #[ORM\ManyToMany(targetEntity: 'GeographicRegion')]
    #[ORM\JoinTable(name: 'pv_project_geographic_region')]
    #[ORM\JoinColumn(name: 'project_id', referencedColumnName: 'id')]
    #[ORM\InverseJoinColumn(name: 'geographic_region_id', referencedColumnName: 'id')]
    #[Groups(['project'])]
    private $geographicRegions;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    #[ORM\ManyToMany(targetEntity: 'Country')]
    #[ORM\JoinTable(name: 'pv_project_country')]
    #[ORM\JoinColumn(name: 'project_id', referencedColumnName: 'id')]
    #[ORM\InverseJoinColumn(name: 'country_id', referencedColumnName: 'id')]
    #[Groups(['project'])]
    private $countries;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    #[ORM\ManyToMany(targetEntity: 'State')]
    #[ORM\JoinTable(name: 'pv_project_state')]
    #[ORM\JoinColumn(name: 'project_id', referencedColumnName: 'id')]
    #[ORM\InverseJoinColumn(name: 'state_id', referencedColumnName: 'id')]
    #[Groups(['project'])]
    private $states;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    #[ORM\ManyToMany(targetEntity: 'Program')]
    #[ORM\JoinTable(name: 'pv_project_program')]
    #[ORM\JoinColumn(name: 'project_id', referencedColumnName: 'id')]
    #[ORM\InverseJoinColumn(name: 'program_id', referencedColumnName: 'id')]
    #[Groups(['project'])]
    private $programs;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    #[ORM\ManyToMany(targetEntity: 'Instrument')]
    #[ORM\JoinTable(name: 'pv_project_instrument')]
    #[ORM\JoinColumn(name: 'project_id', referencedColumnName: 'id')]
    #[ORM\InverseJoinColumn(name: 'instrument_id', referencedColumnName: 'id')]
    #[Groups(['project'])]
    private $instruments;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    #[ORM\ManyToMany(targetEntity: 'BusinessSector')]
    #[ORM\JoinTable(name: 'pv_project_business_sector')]
    #[ORM\JoinColumn(name: 'project_id', referencedColumnName: 'id')]
    #[ORM\InverseJoinColumn(name: 'business_sector_id', referencedColumnName: 'id')]
    #[Groups(['project'])]
    private $businessSectors;

    #[ORM\Column(name: 'project_costs', type: 'string', length: 255, nullable: true)]
    #[Groups(['project'])]
    private $projectCosts;

    #[ORM\Column(name: 'financing', type: 'json')]
    #[Groups(['project'])]
    #[OA\Property(type: 'array', items: new OA\Items(
        properties: [
            new OA\Property(property: 'value', type: 'string'),
            new OA\Property(property: 'id', type: 'string'),
        ],
        type: 'object'
    ))]
    private $financing = [];

    #[ORM\Column(name: 'start_date', type: 'datetime', nullable: true)]
    #[Groups(['project'])]
    private $startDate;

    #[ORM\Column(name: 'end_date', type: 'datetime', nullable: true)]
    #[Groups(['project'])]
    private $endDate;

    #[ORM\Column(name: 'dates', type: 'json')]
    #[Groups(['project'])]
    #[OA\Property(type: 'array', items: new OA\Items(
        properties: [
            new OA\Property(property: 'value', type: 'string'),
            new OA\Property(property: 'label', type: 'string'),
        ],
        type: 'object'
    ))]
    private $dates = [];

    #[ORM\Column(name: 'contacts', type: 'json')]
    #[Groups(['project'])]
    #[OA\Property(type: 'array', items: new OA\Items(
        properties: [
            new OA\Property(property: 'foreignId', type: 'integer'),
            new OA\Property(property: 'name', type: 'string'),
            new OA\Property(property: 'salutation', type: 'string', enum: ['m', 'f', 'd']),
            new OA\Property(property: 'title', type: 'string'),
            new OA\Property(property: 'firstName', type: 'string'),
            new OA\Property(property: 'lastName', type: 'string'),
            new OA\Property(property: 'role', type: 'string'),
            new OA\Property(property: 'country', type: 'string'),
            new OA\Property(property: 'zipCode', type: 'string'),
            new OA\Property(property: 'city', type: 'string'),
            new OA\Property(property: 'email', type: 'string'),
            new OA\Property(property: 'phone', type: 'string'),
            new OA\Property(property: 'phoneMobile', type: 'string'),
            new OA\Property(property: 'public', type: 'boolean'),
        ],
        type: 'object'
    ))]
    private $contacts = [];

    #[ORM\Column(name: 'links', type: 'json')]
    #[Groups(['project'])]
    #[OA\Property(type: 'array', items: new OA\Items(
        properties: [
            new OA\Property(property: 'url', type: 'string'),
            new OA\Property(property: 'value', type: 'string'),
            new OA\Property(property: 'label', type: 'string'),
        ],
        type: 'object'
    ))]
    private $links = [];

    #[ORM\Column(name: 'videos', type: 'json')]
    #[Groups(['project'])]
    #[OA\Property(type: 'array', items: new OA\Items(
        properties: [
            new OA\Property(property: 'url', type: 'string'),
            new OA\Property(property: 'value', type: 'string'),
            new OA\Property(property: 'label', type: 'string'),
        ],
        type: 'object'
    ))]
    private $videos = [];

    #[ORM\Column(name: 'images', type: 'json')]
    #[Groups(['project'])]
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
    #[Groups(['project'])]
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
    #[Groups(['project'])]
    #[OA\Property(properties: [
        new OA\Property(property: 'fr', type: 'object'),
        new OA\Property(property: 'it', type: 'object'),
    ], type: 'object')]
    private $translations = [];

    #[ORM\Column(name: 'search_index', type: 'text', nullable: true)]
    private $searchIndex = null;

    public function __construct()
    {
        $this->topics = new ArrayCollection();
        $this->geographicRegions = new ArrayCollection();
        $this->countries = new ArrayCollection();
        $this->states = new ArrayCollection();
        $this->programs = new ArrayCollection();
        $this->instruments = new ArrayCollection();
        // $this->businessSector = new ArrayCollection();
        $this->localWorkgroups = new ArrayCollection();
        $this->lePeriod = null;
        $this->leFundingCategory = null;
        $this->leFundingArticle = null;
        $this->leFundingMethod = null;
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
     * @return Project
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
     * @return Project
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
     * @return Project
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
     * Set projectCode
     *
     * @param string $projectCode
     *
     * @return Project
     */
    public function setProjectCode($projectCode)
    {
        $this->projectCode = $projectCode;

        return $this;
    }

    /**
     * Get projectCode
     *
     * @return string
     */
    public function getProjectCode()
    {
        return $this->projectCode;
    }

    /**
     * Set foreignId
     *
     * @param string $foreignId
     *
     * @return Project
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
     * Set random
     *
     * @param int|null $random
     *
     * @return Project
     */
    public function setRandom($random)
    {
        $this->random = $random;

        return $this;
    }

    /**
     * Get random
     *
     * @return int|null
     */
    public function getRandom()
    {
        return $this->random;
    }

    /**
     * Set source
     *
     * @param string $source
     *
     * @return Project
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
     * Set title
     *
     * @param string $title
     *
     * @return Project
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
     * Set lat
     *
     * @param string|null $lat
     *
     * @return Project
     */
    public function setLat($lat)
    {
        $this->lat = $lat;

        return $this;
    }

    /**
     * Get lat
     *
     * @return string|null
     */
    public function getLat()
    {
        return $this->lat;
    }

    /**
     * Set lng
     *
     * @param string|null $lng
     *
     * @return Project
     */
    public function setLng($lng)
    {
        $this->lng = $lng;

        return $this;
    }

    /**
     * Get lng
     *
     * @return string|null
     */
    public function getLng()
    {
        return $this->lng;
    }

    /**
     * Set keywords
     *
     * @param string $keywords
     *
     * @return Project
     */
    public function setKeywords($keywords)
    {
        $this->keywords = $keywords;

        return $this;
    }

    /**
     * Get keywords
     *
     * @return string
     */
    public function getKeywords()
    {
        return $this->keywords;
    }

    /**
     * Set description
     *
     * @param string $description
     *
     * @return Project
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
     * Get topics
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getTopics()
    {
        return $this->topics;
    }

    /**
     * Set topics
     *
     * @return $this
     */
    public function setTopics($topics)
    {
        $this->topics = $topics;

        return $this;
    }

    /**
     * Add to topics
     *
     * @param $topic
     * @return Project
     */
    public function addTopic($topic)
    {
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
    public function removeTopic($topics)
    {
        $this->topics->removeElement($topics);
    }

    /**
     * Get tags
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getTags()
    {
        return $this->tags;
    }

    /**
     * Set tags
     *
     * @return $this
     */
    public function setTags($tags)
    {
        $this->tags = $tags;

        return $this;
    }

    /**
     * Add to tags
     *
     * @param $tag
     * @return Project
     */
    public function addTag($tag)
    {
        if (!$this->tags->contains($tag)) {
            $this->tags->add($tag);
        }

        return $this;
    }

    /**
     * Remove tags
     *
     * @param $tags
     */
    public function removeTag($tags)
    {
        $this->tags->removeElement($tags);
    }

    /**
     * Get geographicRegions
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getGeographicRegions()
    {
        return $this->geographicRegions;
    }

    /**
     * Set geographicRegions
     *
     * @return $this
     */
    public function setGeographicRegions($geographicRegions)
    {
        $this->geographicRegions = $geographicRegions;

        return $this;
    }

    /**
     * Add to geographicRegions
     *
     * @param $geographicRegion
     * @return Project
     */
    public function addGeographicRegion($geographicRegion)
    {
        if (!$this->geographicRegions->contains($geographicRegion)) {
            $this->geographicRegions->add($geographicRegion);
        }

        return $this;
    }

    /**
     * Remove geographicRegion
     *
     * @param $geographicRegion
     */
    public function removeGeographicRegion($geographicRegion)
    {
        $this->geographicRegions->removeElement($geographicRegion);
    }

    /**
     * Get countries
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getCountries()
    {
        return $this->countries;
    }

    /**
     * Set countries
     *
     * @return $this
     */
    public function setCountries($countries)
    {
        $this->countries = $countries;

        return $this;
    }

    /**
     * Add to countries
     *
     * @param $country
     * @return Project
     */
    public function addCountry($country)
    {
        if (!$this->countries->contains($country)) {
            $this->countries->add($country);
        }

        return $this;
    }

    /**
     * Remove countries
     *
     * @param $countries
     */
    public function removeCountry($countries)
    {
        $this->countries->removeElement($countries);
    }

    /**
     * Get states
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getStates()
    {
        return $this->states;
    }

    /**
     * Set states
     *
     * @return $this
     */
    public function setStates($states)
    {
        $this->states = $states;

        return $this;
    }

    /**
     * Add to states
     *
     * @param $state
     * @return Project
     */
    public function addState($state)
    {
        if (!$this->states->contains($state)) {
            $this->states->add($state);
        }

        return $this;
    }

    /**
     * Remove state
     *
     * @param $state
     */
    public function removeState($state)
    {
        $this->states->removeElement($state);
    }

    /**
     * Get programs
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getPrograms()
    {
        return $this->programs;
    }

    /**
     * Set programs
     *
     * @return $this
     */
    public function setPrograms($programs)
    {
        $this->programs = $programs;

        return $this;
    }

    /**
     * Add to programs
     *
     * @param $program
     * @return Project
     */
    public function addProgram($program)
    {
        if (!$this->programs->contains($program)) {
            $this->programs->add($program);
        }

        return $this;
    }

    /**
     * Remove program
     *
     * @param $program
     */
    public function removeProgram($program)
    {
        $this->programs->removeElement($program);
    }

    /**
     * Get instruments
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getInstruments()
    {
        return $this->instruments;
    }

    /**
     * Set instruments
     *
     * @return $this
     */
    public function setInstruments($instruments)
    {
        $this->instruments = $instruments;

        return $this;
    }

    /**
     * Add to instruments
     *
     * @param $instrument
     * @return Project
     */
    public function addInstrument($instrument)
    {
        if (!$this->instruments->contains($instrument)) {
            $this->instruments->add($instrument);
        }

        return $this;
    }

    /**
     * Remove instrument
     *
     * @param $instrument
     */
    public function removeInstrument($instrument)
    {
        $this->instruments->removeElement($instrument);
    }

    /**
     * Get businessSectors
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getBusinessSectors()
    {
        return $this->businessSectors;
    }

    /**
     * Set businessSectors
     *
     * @return $this
     */
    public function setBusinessSectors($businessSectors)
    {
        $this->businessSectors = $businessSectors;

        return $this;
    }

    /**
     * Add to businessSectors
     *
     * @param $businessSector
     * @return Project
     */
    public function addBusinessSector($businessSector)
    {
        if (!$this->businessSectors->contains($businessSector)) {
            $this->businessSectors->add($businessSector);
        }

        return $this;
    }

    /**
     * Remove businessSector
     *
     * @param $businessSector
     */
    public function removeBusinessSector($businessSector)
    {
        $this->businessSectors->removeElement($businessSector);
    }

    /**
     * Set projectCosts
     *
     * @param string $projectCosts
     *
     * @return Project
     */
    public function setProjectCosts($projectCosts)
    {
        $this->projectCosts = $projectCosts;

        return $this;
    }

    /**
     * Get projectCosts
     *
     * @return string
     */
    public function getProjectCosts()
    {
        return $this->projectCosts;
    }

    /**
     * Set financing
     *
     * @param array $financing
     *
     * @return Project
     */
    public function setFinancing($financing)
    {
        $this->financing = $financing;

        return $this;
    }

    /**
     * Get financing
     *
     * @return array
     */
    public function getFinancing()
    {
        return $this->financing;
    }

    /**
     * Set startDate
     *
     * @param \DateTime $startDate
     *
     * @return Project
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
     * @return Project
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
     * Set dates
     *
     * @param array $dates
     *
     * @return Project
     */
    public function setDates($dates)
    {
        $this->dates = $dates;

        return $this;
    }

    /**
     * Get dates
     *
     * @return array
     */
    public function getDates()
    {
        return $this->dates;
    }

    /**
     * Set contacts
     *
     * @param array $contacts
     *
     * @return Project
     */
    public function setContacts($contacts)
    {
        $this->contacts = $contacts;

        return $this;
    }

    /**
     * Get contacts
     *
     * @return array
     */
    public function getContacts()
    {
        return $this->contacts;
    }

    /**
     * Set links
     *
     * @param array $links
     *
     * @return Project
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
     * @return Project
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
     * @return Project
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
     * @return Project
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
     * @return Project
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
     * Set searchIndex
     *
     * @param string|null $searchIndex
     *
     * @return Project
     */
    public function setSearchIndex(?string $searchIndex)
    {
        $this->searchIndex = $searchIndex;

        return $this;
    }

    /**
     * Get searchIndex
     *
     * @return string|null
     */
    public function getSearchIndex(): ?string
    {
        return $this->searchIndex;
    }

    /**
     * Get the local workgroup.
     *
     * @return LocalWorkgroup|null
     */
    public function getLocalWorkgroup(): ?LocalWorkgroup
    {
        return $this->localWorkgroup;
    }

    /**
     * Set the local workgroup.
     *
     * @param LocalWorkgroup|null $localWorkgroup
     * @return self
     */
    public function setLocalWorkgroup(?LocalWorkgroup $localWorkgroup): self
    {
        $this->localWorkgroup = $localWorkgroup;

        return $this;
    }

    /**
     * @return Collection|LocalWorkgroup[]
     */
    public function getLocalWorkgroups(): Collection
    {
        return $this->localWorkgroups;
    }

    public function addLocalWorkgroup(LocalWorkgroup $localWorkgroup): self
    {
        if (!$this->localWorkgroups->contains($localWorkgroup)) {
            $this->localWorkgroups[] = $localWorkgroup;
        }
        return $this;
    }

    public function removeLocalWorkgroup(LocalWorkgroup $localWorkgroup): self
    {
        $this->localWorkgroups->removeElement($localWorkgroup);
        return $this;
    }

    public function setLocalWorkgroups(Collection $localWorkgroups): self
    {
        $this->localWorkgroups = $localWorkgroups;
        return $this;
    }

    /**
     * Check if the project is a case study.
     *
     * @return bool
     */
    public function isCaseStudy(): bool
    {
        return $this->caseStudy;
    }

    /**
     * Set the project as a case study.
     *
     * @param bool $caseStudy
     * @return self
     */
    public function setCaseStudy(bool $caseStudy): self
    {
        $this->caseStudy = $caseStudy;

        return $this;
    }

    /**
     * Get the value of cooperationProjectAt.
     *
     * @return bool|null
     */
    public function getcooperationProjectAt(): ?bool
    {
        return $this->cooperationProjectAt;
    }

    /**
     * Set the value of cooperationProjectAt.
     *
     * @param bool|null $cooperationProjectAt
     * @return self
     */
    public function setcooperationProjectAt(?bool $cooperationProjectAt): self
    {
        $this->cooperationProjectAt = $cooperationProjectAt;

        return $this;
    }

    /**
     * Get the value of cooperationProjectEu.
     *
     * @return bool|null
     */
    public function getcooperationProjectEu(): ?bool
    {
        return $this->cooperationProjectEu;
    }

    /**
     * Set the value of cooperationProjectEu.
     *
     * @param bool|null $cooperationProjectEu
     * @return self
     */
    public function setcooperationProjectEu(?bool $cooperationProjectEu): self
    {
        $this->cooperationProjectEu = $cooperationProjectEu;

        return $this;
    }

    /**
     * Check if the project has a synergy to other EU policies.
     *
     * @return bool
     */
    public function isSynergy(): bool
    {
        return $this->synergy;
    }

    /**
     * Set the project synergy to other EU policies.
     *
     * @param bool $synergy
     * @return self
     */
    public function setSynergy(bool $synergy): self
    {
        $this->synergy = $synergy;

        return $this;
    }

    /**
     * Check if the project goal contributes to other european or international policies.
     *
     * @return bool
     */
    public function isSynergyGoal(): bool
    {
        return $this->synergyGoal;
    }

    /**
     * Set the project goal contributes to other european or international policies.
     *
     * @param bool $synergyGoal
     * @return self
     */
    public function setSynergyGoal(bool $synergyGoal): self
    {
        $this->synergyGoal = $synergyGoal;

        return $this;
    }

    /**
     * Get synergyFundTags
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getSynergyFundTags(): Collection
    {
        return $this->synergyFundTags;
    }

    /**
     * Set synergyFundTags
     *
     * @param \Doctrine\Common\Collections\Collection $synergyFundTags
     * @return $this
     */
    public function setSynergyFundTags(Collection $synergyFundTags): self
    {
        $this->synergyFundTags = $synergyFundTags;

        return $this;
    }

    /**
     * Add a synergy fund tag
     *
     * @param Tag $synergyFundTag
     * @return $this
     */
    public function addSynergyFundTag(Tag $synergyFundTag): self
    {
        if (!$this->synergyFundTags->contains($synergyFundTag)) {
            $this->synergyFundTags[] = $synergyFundTag;
        }

        return $this;
    }

    /**
     * Remove a synergy fund tag
     *
     * @param Tag $synergyFundTag
     */
    public function removeSynergyFundTag(Tag $synergyFundTag): void
    {
        $this->synergyFundTags->removeElement($synergyFundTag);
    }

    /**
     * Get synergyGoalTags
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getSynergyGoalTags(): Collection
    {
        return $this->synergyGoalTags;
    }

    /**
     * Set synergyGoalTags
     *
     * @param \Doctrine\Common\Collections\Collection $synergyGoalTags
     * @return $this
     */
    public function setSynergyGoalTags(Collection $synergyGoalTags): self
    {
        $this->synergyGoalTags = $synergyGoalTags;

        return $this;
    }

    /**
     * Add a synergy goal tag
     *
     * @param Tag $synergyGoalTag
     * @return $this
     */
    public function addSynergyGoalTag(Tag $synergyGoalTag): self
    {
        if (!$this->synergyGoalTags->contains($synergyGoalTag)) {
            $this->synergyGoalTags[] = $synergyGoalTag;
        }

        return $this;
    }

    /**
     * Remove a synergy goal tag
     *
     * @param Tag $synergyGoalTag
     */
    public function removeSynergyGoalTag(Tag $synergyGoalTag): void
    {
        $this->synergyGoalTags->removeElement($synergyGoalTag);
    }

    /**
     * Set lePeriod
     *
     * @param LEPeriod $lePeriod
     *
     * @return Project
     */
    public function setLePeriod(?LEPeriod $lePeriod): static
    {
        $this->lePeriod = $lePeriod;

        return $this;
    }

    /**
     * Get lePeriod
     *
     * @return LEPeriod
     */
    public function getLePeriod(): ?LEPeriod
    {
        return $this->lePeriod;
    }

    /**
     * Set leFundingCategory
     *
     * @param LEFundingCategory $leFundingCategory
     *
     * @return Project
     */
    public function setLEFundingCategory($leFundingCategory): static
    {
        $this->leFundingCategory = $leFundingCategory;

        return $this;
    }

    /**
     * Get leFundingCategory
     *
     * @return LEFundingCategory
     */
    public function getLEFundingCategory(): ?LEFundingCategory
    {
        return $this->leFundingCategory;
    }

    /**
     * Set leFundingArticle
     *
     * @param LEFundingArticle $leFundingArticle
     *
     * @return Project
     */
    public function setLEFundingArticle($leFundingArticle): static
    {
        $this->leFundingArticle = $leFundingArticle;

        return $this;
    }

    /**
     * Get leFundingArticle
     *
     * @return LEFundingArticle
     */
    public function getLEFundingArticle(): ?LEFundingArticle
    {
        return $this->leFundingArticle;
    }

    /**
     * Set leFundingMethod
     *
     * @param LEFundingMethod $leFundingMethod
     *
     * @return Project
     */
    public function getLEFundingMethod(): ?LEFundingMethod
    {
        return $this->leFundingMethod;
    }

    /**
     * Get leFundingMethod
     *
     * @return LEFundingMethod
     */
    public function setLEFundingMethod($leFundingMethod): static
    {
        $this->leFundingMethod = $leFundingMethod;

        return $this;
    }

    /**
     * Set exemplary
     *
     * @param string $exemplary
     *
     * @return Project
     */
    public function getExemplary(): ?string
    {
        return $this->exemplary;
    }


    public function setExemplary(?string $exemplary): static
    {
        $this->exemplary = $exemplary;

        return $this;
    }

    public function getInitialContext(): ?string
    {
        return $this->initialContext;
    }

    public function setInitialContext(?string $initialContext): static
    {
        $this->initialContext = $initialContext;

        return $this;
    }

    public function getInitialContextGoals(): ?string
    {
        return $this->initialContextGoals;
    }

    public function setInitialContextGoals(?string $initialContextGoals): static
    {
        $this->initialContextGoals = $initialContextGoals;

        return $this;
    }

    public function getAdditionalValue(): ?string
    {
        return $this->additionalValue;
    }

    public function setAdditionalValue(?string $additionalValue): static
    {
        $this->additionalValue = $additionalValue;

        return $this;
    }

    public function getAdditionalValueResult(): ?string
    {
        return $this->additionalValueResult;
    }

    public function setAdditionalValueResult(?string $additionalValueResult): static
    {
        $this->additionalValueResult = $additionalValueResult;

        return $this;
    }

    public function getInnovations(): ?string
    {
        return $this->innovations;
    }

    public function setInnovations(?string $innovations): static
    {
        $this->innovations = $innovations;

        return $this;
    }

    public function getIntegrationYoungCitizen(): ?string
    {
        return $this->integrationYoungCitizen;
    }

    public function setIntegrationYoungCitizen(?string $integrationYoungCitizen): static
    {
        $this->integrationYoungCitizen = $integrationYoungCitizen;

        return $this;
    }

    public function getIntegrationFemaleCitizen(): ?string
    {
        return $this->integrationFemaleCitizen;
    }

    public function setIntegrationFemaleCitizen(?string $integrationFemaleCitizen): static
    {
        $this->integrationFemaleCitizen = $integrationFemaleCitizen;

        return $this;
    }

    public function getIntegrationMinorities(): ?string
    {
        return $this->integrationMinorities;
    }

    public function setIntegrationMinorities(?string $integrationMinorities): static
    {
        $this->integrationMinorities = $integrationMinorities;

        return $this;
    }

    public function getLearningExperience(): ?string
    {
        return $this->learningExperience;
    }

    public function setLearningExperience(?string $learningExperience): static
    {
        $this->learningExperience = $learningExperience;

        return $this;
    }

    public function getTransferable(): ?string
    {
        return $this->transferable;
    }

    public function setTransferable(?string $transferable): static
    {
        $this->transferable = $transferable;

        return $this;
    }

    public function getTransferDetails(): ?string
    {
        return $this->transferDetails;
    }

    public function setTransferDetails(?string $transferDetails): static
    {
        $this->transferDetails = $transferDetails;

        return $this;
    }
}
