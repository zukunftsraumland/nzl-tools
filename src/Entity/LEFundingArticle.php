<?php

namespace App\Entity;

use Symfony\Component\Serializer\Annotation\Groups;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Table(name: 'pv_le_funding_article')]
#[ORM\Entity]
class LEFundingArticle
{
    #[ORM\Id, ORM\GeneratedValue, ORM\Column(type: 'integer')]
    #[Groups(['project'])]
    private $id;

    #[ORM\Column(type: 'string')]
    #[Groups(['project'])]
    private $name;

    #[ORM\ManyToOne(targetEntity: LEFundingCategory::class, inversedBy: 'articles')]
    private $category;

    #[ORM\OneToMany(mappedBy: 'article', targetEntity: LEFundingMethod::class)]
    private $methods;

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
     * Get the value of category
     *
     * @return LEFundingCategory|null
     */
    public function getCategory(): ?LEFundingCategory
    {
        return $this->category;
    }

    /**
     * Set the value of category
     *
     * @param LEFundingCategory|null $category
     * @return self
     */
    public function setCategory(?LEFundingCategory $category): self
    {
        $this->category = $category;

        return $this;
    }

    /**
     * Get the value of methods
     *
     * @return Collection|LEFundingMethod[]
     */
    public function getMethods()
    {
        return $this->methods;
    }

    /**
     * Set the value of methods
     *
     * @param Collection|LEFundingMethod[] $methods
     * @return self
     */
    public function setMethods($methods): self
    {
        $this->methods = $methods;

        return $this;
    }
}