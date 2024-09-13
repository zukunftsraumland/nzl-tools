<?php

namespace App\Entity;

use Symfony\Component\Serializer\Annotation\Groups;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Table(name: 'pv_le_period')]
#[ORM\Entity]
class LEPeriod
{
    #[ORM\Id, ORM\GeneratedValue, ORM\Column(type: 'integer')]
    #[Groups(['project'])]
    private $id;

    #[ORM\Column(type: 'string')]
    #[Groups(['project'])]
    private $name;

    #[ORM\OneToMany(mappedBy: 'period', targetEntity: LEFundingCategory::class)]
    private $categories;

    /**
     * Get the value of id
     *
     * @return int|null
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * Get the value of name
     *
     * @return string|null
     */
    public function getName(): ?string
    {
        return $this->name;
    }

    /**
     * Set the value of name
     *
     * @param string $name
     * @return self
     */
    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get the value of categories
     *
     * @return Collection|LEFundingCategory[]
     */
    public function getCategories()
    {
        return $this->categories;
    }

    /**
     * Set the value of categories
     *
     * @param Collection|LEFundingCategory[] $categories
     * @return self
     */
    public function setCategories($categories): self
    {
        $this->categories = $categories;

        return $this;
    }
}