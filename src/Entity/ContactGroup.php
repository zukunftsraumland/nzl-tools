<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use OpenApi\Attributes as OA;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * ContactGroup
 */
#[ORM\Table(name: 'pv_contact_group')]
#[ORM\Entity(repositoryClass: 'App\Repository\ContactGroupRepository')]
class ContactGroup
{

    /**
     * @var int
     */
    #[ORM\Column(name: 'id', type: 'integer')]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    #[Groups(['id', 'contact_group'])]
    private $id;

    /**
     * @var bool
     */
    #[ORM\Column(name: 'is_public', type: 'boolean')]
    #[Groups(['contact_group'])]
    private $isPublic;

    /**
     * @var int|null
     */
    #[ORM\Column(name: 'position', type: 'integer', nullable: true)]
    #[Groups(['contact_group'])]
    private $position;

    /**
     * @var \DateTime
     */
    #[ORM\Column(name: 'created_at', type: 'datetime')]
    #[Groups(['contact_group'])]
    private $createdAt;

    /**
     * @var \DateTime
     */
    #[ORM\Column(name: 'updated_at', type: 'datetime', nullable: true)]
    #[Groups(['contact_group'])]
    private $updatedAt;

    /**
     * @var string
     */
    #[ORM\Column(name: 'name', type: 'text', nullable: true)]
    #[Groups(['contact_group'])]
    private $name;

    #[ORM\ManyToOne(targetEntity: 'ContactGroup', inversedBy: 'children')]
    #[ORM\JoinColumn(name: 'parent_id', referencedColumnName: 'id')]
    #[Groups(['contact_group'])]
    private ?ContactGroup $parent = null;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    #[ORM\OneToMany(targetEntity: 'ContactGroup', mappedBy: 'parent')]
    #[Groups(['contact_group_children'])]
    private $children;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    #[ORM\ManyToMany(targetEntity: 'Contact', mappedBy: 'contactGroups')]
    #[Groups(['contact_group'])]
    private $contacts;

    #[ORM\ManyToMany(targetEntity: 'Employment', inversedBy: 'contactGroups')]
    #[ORM\JoinTable(name: 'pv_contact_group_employment')]
    #[ORM\JoinColumn(name: 'contact_group_id', referencedColumnName: 'id')]
    #[ORM\InverseJoinColumn(name: 'employment_id', referencedColumnName: 'id')]
    #[Groups(['contact_group'])]
    private $employments;

    #[ORM\Column(name: 'tpoint_id', type: 'integer', nullable: true)]
    #[Groups(['contact_group'])]
    private $tpointId;

    #[ORM\Column(name: 'tpoint_checksum', type: 'string', length: 128, nullable: true)]
    #[Groups(['contact_group'])]
    private $tpointChecksum;

    /**
     * @var string
     */
    #[ORM\Column(name: 'context', type: 'string', length: 255, nullable: true)]
    #[Groups(['contact_group'])]
    private $context;

    /**
     * @var array
     */
    #[ORM\Column(name: 'synonyms', type: 'json')]
    #[Groups(['contact_group'])]
    #[OA\Property(type: 'array', items: new OA\Items(type: 'string'))]
    private $synonyms = [];

    /**
     * @var array
     */
    #[ORM\Column(name: 'translations', type: 'json')]
    #[Groups(['contact_group'])]
    #[OA\Property(properties: [
        new OA\Property(property: 'fr', type: 'string'),
        new OA\Property(property: 'it', type: 'string'),
    ], type: 'object')]
    private $translations = [];

    public function __construct()
    {
        $this->children = new ArrayCollection();
        $this->contacts = new ArrayCollection();
        $this->employments = new ArrayCollection();
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
     * @return ContactGroup
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
     * @return ContactGroup
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
     * @return ContactGroup
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
     * @return ContactGroup
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
     * @return ContactGroup
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

    public function getParent(): ?ContactGroup
    {
        return $this->parent;
    }

    public function setParent(?ContactGroup $parent): self
    {
        $this->parent = $parent;

        return $this;
    }

    /**
     * Get children
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getChildren()
    {
        return $this->children;
    }

    /**
     * Set children
     *
     * @return ContactGroup
     */
    public function setChildren($children) {
        $this->children = $children;

        return $this;
    }

    /**
     * Add to children
     *
     * @param $child
     * @return ContactGroup
     */
    public function addChild($child)
    {
        if (!$this->children->contains($child)) {
            $this->children->add($child);
            $child->setParent($this);
        }

        return $this;
    }

    /**
     * Remove children
     *
     * @param $child
     */
    public function removeChild($child)
    {
        if ($this->children->contains($child)) {
            $this->children->removeElement($child);

            if ($child->getParent() === $this) {
                $child->setParent(null);
            }
        }
    }

    /**
     * Get contacts
     *
     * @return array
     */
    public function getContacts() {
        return array_values($this->contacts->toArray());
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
            $contact->addContactGroup($this);
        }

        return $this;
    }

    /**
     * Remove contacts
     *
     * @param $contact
     */
    public function removeContact($contact) {
        if ($this->contacts->contains($contact)) {
            $this->contacts->removeElement($contact);
            $contact->removeContactGroup($this);
        }
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
     * Add to employments
     *
     * @param $employment
     * @return self
     */
    public function addEmployment($employment) {
        if (!$this->employments->contains($employment)) {
            $this->employments->add($employment);
            $employment->addContactGroup($this);
        }

        return $this;
    }

    /**
     * Remove employments
     *
     * @param $employment
     */
    public function removeEmployment($employment) {
        if ($this->employments->contains($employment)) {
            $this->employments->removeElement($employment);
            $employment->removeContactGroup($this);
        }
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
     * @return ContactGroup
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
     * @return ContactGroup
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
     * @return ContactGroup
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
     * @return ContactGroup
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
