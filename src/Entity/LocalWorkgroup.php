<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * LocalWorkgroup
 */
#[ORM\Table(name: 'pv_local_workgroup')]
#[ORM\Entity(repositoryClass: 'App\Repository\LocalWorkgroupRepository')]
class LocalWorkgroup
{
    #[ORM\Column(name: 'id', type: 'integer')]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    #[Groups(['id', 'localworkgroup'])]
    private $id;

    #[ORM\Column(name: 'name', type: 'string', length: 255)]
    #[Groups(['localworkgroup'])]
    private $name;

    public function getId(): int
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }
}
