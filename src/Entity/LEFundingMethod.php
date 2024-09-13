<?php 

namespace App\Entity;

use Symfony\Component\Serializer\Annotation\Groups;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Table(name: 'pv_le_funding_method')]
#[ORM\Entity]
class LEFundingMethod
{
    #[ORM\Id, ORM\GeneratedValue, ORM\Column(type: 'integer')]
    #[Groups(['project'])]
    private $id;

    #[ORM\Column(type: 'string')]
    #[Groups(['project'])]
    private $name;

    #[ORM\ManyToOne(targetEntity: LEFundingArticle::class, inversedBy: 'methods')]
    private $article;

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
     * Get the value of article
     *
     * @return LEFundingArticle|null
     */
    public function getArticle(): ?LEFundingArticle
    {
        return $this->article;
    }

    /**
     * Set the value of article
     *
     * @param LEFundingArticle|null $article
     * @return self
     */
    public function setArticle(?LEFundingArticle $article): self
    {
        $this->article = $article;

        return $this;
    }
}