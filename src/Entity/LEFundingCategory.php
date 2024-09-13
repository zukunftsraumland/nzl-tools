<?php

namespace App\Entity;

use Symfony\Component\Serializer\Annotation\Groups;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Table(name: 'pv_le_funding_category')]
#[ORM\Entity]
class LEFundingCategory
{
    #[ORM\Id, ORM\GeneratedValue, ORM\Column(type: 'integer')]
    #[Groups(['project'])]
    private $id;

    #[ORM\Column(type: 'string')]
    #[Groups(['project'])]
    private $name;

    #[ORM\ManyToOne(targetEntity: LEPeriod::class, inversedBy: 'categories')]
    private $period;

    #[ORM\OneToMany(mappedBy: 'category', targetEntity: LEFundingArticle::class)]
    private $articles;

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
     * Get the value of period
     *
     * @return LEPeriod|null
     */
    public function getPeriod(): ?LEPeriod
    {
        return $this->period;
    }

    /**
     * Set the value of period
     *
     * @param LEPeriod|null $period
     * @return self
     */
    public function setPeriod(?LEPeriod $period): self
    {
        $this->period = $period;

        return $this;
    }

    /**
     * Get the value of articles
     *
     * @return Collection|LEFundingArticle[]
     */
    public function getArticles()
    {
        return $this->articles;
    }

    /**
     * Set the value of articles
     *
     * @param Collection|LEFundingArticle[] $articles
     * @return self
     */
    public function setArticles($articles): self
    {
        $this->articles = $articles;

        return $this;
    }
}