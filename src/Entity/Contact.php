<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use OpenApi\Attributes as OA;

/**
 * Contact
 */
#[ORM\Table(name: 'pv_contact')]
#[ORM\Entity(repositoryClass: 'App\Repository\ContactRepository')]
#[ORM\Index(columns: ['context'], name: 'context_idx')]
#[ORM\Index(columns: ['type'], name: 'type_idx')]
class Contact
{

    #[ORM\Column(name: 'id', type: 'integer')]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    #[Groups(['id', 'contact'])]
    private $id;

    #[ORM\Column(name: 'is_public', type: 'boolean')]
    #[Groups(['contact'])]
    private $isPublic;

    #[ORM\Column(name: 'created_at', type: 'datetime')]
    #[Groups(['contact'])]
    private $createdAt;

    #[ORM\Column(name: 'updated_at', type: 'datetime', nullable: true)]
    #[Groups(['contact'])]
    private $updatedAt;

    #[ORM\Column(name: 'type', type: 'string', length: 64, nullable: true)]
    #[Groups(['contact'])]
    private $type;

    #[Groups(['contact'])]
    private $name;

    #[ORM\Column(name: 'company_name', type: 'string', length: 128, nullable: true)]
    #[Groups(['contact'])]
    private $companyName;

    #[ORM\Column(name: 'gender', type: 'string', length: 32, nullable: true)]
    #[Groups(['contact'])]
    private $gender;

    #[ORM\Column(name: 'academic_title', type: 'string', length: 32, nullable: true)]
    #[Groups(['contact'])]
    private $academicTitle;

    #[ORM\Column(name: 'first_name', type: 'string', length: 128, nullable: true)]
    #[Groups(['contact'])]
    private $firstName;

    #[ORM\Column(name: 'last_name', type: 'string', length: 128, nullable: true)]
    #[Groups(['contact'])]
    private $lastName;

    #[ORM\Column(name: 'street', type: 'string', length: 128, nullable: true)]
    #[Groups(['contact'])]
    private $street;

    #[ORM\Column(name: 'zip_code', type: 'string', length: 16, nullable: true)]
    #[Groups(['contact'])]
    private $zipCode;

    #[ORM\Column(name: 'city', type: 'string', length: 128, nullable: true)]
    #[Groups(['contact'])]
    private $city;

    #[ORM\Column(name: 'email', type: 'string', length: 128, nullable: true)]
    #[Groups(['contact'])]
    private $email;

    #[ORM\Column(name: 'phone', type: 'string', length: 128, nullable: true)]
    #[Groups(['contact'])]
    private $phone;

    #[ORM\Column(name: 'website', type: 'string', length: 128, nullable: true)]
    #[Groups(['contact'])]
    private $website;

    #[ORM\Column(name: 'description', type: 'text', nullable: true)]
    #[Groups(['contact'])]
    private $description;

    #[ORM\Column(name: 'tpoint_id', type: 'integer', nullable: true)]
    #[Groups(['contact'])]
    private $tpointId;

    #[ORM\Column(name: 'tpoint_checksum', type: 'string', length: 128, nullable: true)]
    #[Groups(['contact'])]
    private $tpointChecksum;

    #[ORM\Column(name: 'context', type: 'string', length: 255, nullable: true)]
    #[Groups(['contact'])]
    private $context;

    #[ORM\Column(name: 'translations', type: 'json')]
    #[Groups(['contact'])]
    #[OA\Property(properties: [
        new OA\Property(property: 'fr', type: 'string'),
        new OA\Property(property: 'it', type: 'string'),
    ], type: 'object')]
    private $translations = [];

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    #[ORM\OneToMany(targetEntity: 'Employment', cascade: ['persist', 'remove'], orphanRemoval: true, mappedBy: 'employee')]
    #[ORM\JoinColumn(name: 'id', referencedColumnName: 'employee_id')]
    #[ORM\InverseJoinColumn(name: 'employee_id', referencedColumnName: 'id')]
    #[Groups(['contact'])]
    private $employments;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    #[ORM\OneToMany(targetEntity: 'Employment', cascade: ['persist', 'remove'], orphanRemoval: true, mappedBy: 'company')]
    #[ORM\JoinColumn(name: 'id', referencedColumnName: 'company_id')]
    #[ORM\InverseJoinColumn(name: 'company_id', referencedColumnName: 'id')]
    #[Groups(['contact'])]
    private $employees;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    #[ORM\ManyToMany(targetEntity: 'ContactGroup')]
    #[ORM\JoinTable(name: 'pv_contact_contact_group')]
    #[ORM\JoinColumn(name: 'contact_id', referencedColumnName: 'id')]
    #[ORM\InverseJoinColumn(name: 'contact_group_id', referencedColumnName: 'id')]
    #[Groups(['contact'])]
    private $contactGroups;

    public function __construct()
    {
        $this->employments = new ArrayCollection();
        $this->employees = new ArrayCollection();
        $this->contactGroups = new ArrayCollection();
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
     * @return Contact
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
     * @return Contact
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
     * @return Contact
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
     * @return Contact
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
     * Get name
     *
     * @return string
     */
    public function getName()
    {
        if($this->type === 'company') {
            return $this->companyName;
        }
        return $this->firstName . ' ' . $this->lastName;
    }

    /**
     * Set companyName
     *
     * @param string $companyName
     *
     * @return Contact
     */
    public function setCompanyName($companyName)
    {
        $this->companyName = $companyName;

        return $this;
    }

    /**
     * Get companyName
     *
     * @return string
     */
    public function getCompanyName()
    {
        return $this->companyName;
    }

    /**
     * Set gender
     *
     * @param string $gender
     *
     * @return Contact
     */
    public function setGender($gender)
    {
        $this->gender = $gender;

        return $this;
    }

    /**
     * Get gender
     *
     * @return string
     */
    public function getGender()
    {
        return $this->gender;
    }

    /**
     * Set academicTitle
     *
     * @param string $academicTitle
     *
     * @return Contact
     */
    public function setAcademicTitle($academicTitle)
    {
        $this->academicTitle = $academicTitle;

        return $this;
    }

    /**
     * Get academicTitle
     *
     * @return string
     */
    public function getAcademicTitle()
    {
        return $this->academicTitle;
    }

    /**
     * Set firstName
     *
     * @param string $firstName
     *
     * @return Contact
     */
    public function setFirstName($firstName)
    {
        $this->firstName = $firstName;

        return $this;
    }

    /**
     * Get firstName
     *
     * @return string
     */
    public function getFirstName()
    {
        return $this->firstName;
    }

    /**
     * Set lastName
     *
     * @param string $lastName
     *
     * @return Contact
     */
    public function setLastName($lastName)
    {
        $this->lastName = $lastName;

        return $this;
    }

    /**
     * Get lastName
     *
     * @return string
     */
    public function getLastName()
    {
        return $this->lastName;
    }

    /**
     * Set street
     *
     * @param string $street
     *
     * @return Contact
     */
    public function setStreet($street)
    {
        $this->street = $street;

        return $this;
    }

    /**
     * Get street
     *
     * @return string
     */
    public function getStreet()
    {
        return $this->street;
    }

    /**
     * Set zipCode
     *
     * @param string $zipCode
     *
     * @return Contact
     */
    public function setZipCode($zipCode)
    {
        $this->zipCode = $zipCode;

        return $this;
    }

    /**
     * Get zipCode
     *
     * @return string
     */
    public function getZipCode()
    {
        return $this->zipCode;
    }

    /**
     * Set city
     *
     * @param string $city
     *
     * @return Contact
     */
    public function setCity($city)
    {
        $this->city = $city;

        return $this;
    }

    /**
     * Get city
     *
     * @return string
     */
    public function getCity()
    {
        return $this->city;
    }

    /**
     * Set email
     *
     * @param string $email
     *
     * @return Contact
     */
    public function setEmail($email)
    {
        $this->email = $email;

        return $this;
    }

    /**
     * Get email
     *
     * @return string
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * Set phone
     *
     * @param string $phone
     *
     * @return Contact
     */
    public function setPhone($phone)
    {
        $this->phone = $phone;

        return $this;
    }

    /**
     * Get phone
     *
     * @return string
     */
    public function getPhone()
    {
        return $this->phone;
    }

    /**
     * Set website
     *
     * @param string $website
     *
     * @return Contact
     */
    public function setWebsite($website)
    {
        $this->website = $website;

        return $this;
    }

    /**
     * Get website
     *
     * @return string
     */
    public function getWebsite()
    {
        return $this->website;
    }

    /**
     * Set description
     *
     * @param string $description
     *
     * @return Contact
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
     * Set tpointId
     *
     * @param int|null $tpointId
     *
     * @return Contact
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
     * @return Contact
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
     * @return Contact
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
     * @return Contact
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
     * @return Contact
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
     * Get employees
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getEmployees() {
        return $this->employees;
    }

    /**
     * Set employees
     *
     * @return self
     */
    public function setEmployees($employees) {
        $this->employees = $employees;

        return $this;
    }

    /**
     * Get employments
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getEmployments() {
        return $this->employments;
    }

    /**
     * Set employments
     *
     * @return self
     */
    public function setEmployments($employments) {
        $this->employments = $employments;

        return $this;
    }

    /**
     * Get contactGroups
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getContactGroups() {
        return $this->contactGroups;
    }

    /**
     * Set contactGroups
     *
     * @return self
     */
    public function setContactGroups($contactGroups) {
        $this->contactGroups = $contactGroups;

        return $this;
    }

    /**
     * Add to contactGroups
     *
     * @param $contactGroup
     * @return self
     */
    public function addContactGroup($contactGroup) {
        if (!$this->contactGroups->contains($contactGroup)) {
            $this->contactGroups->add($contactGroup);
        }

        return $this;
    }

    /**
     * Remove contactGroups
     *
     * @param $contactGroups
     */
    public function removeContactGroup($contactGroups) {
        $this->contactGroups->removeElement($contactGroups);
    }
}

