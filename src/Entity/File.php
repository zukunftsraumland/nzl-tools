<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * File
 */
#[ORM\Table(name: 'pv_file')]
#[ORM\Entity(repositoryClass: 'App\Repository\FileRepository')]
#[ORM\Index(columns: ['hash'], name: 'hash_idx')]
class File
{

    #[ORM\Column(name: 'id', type: 'integer')]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    #[Groups(['id', 'file'])]
    private $id;

    #[ORM\Column(name: 'created_at', type: 'datetime')]
    #[Groups(['file'])]
    private $createdAt;

    #[ORM\Column(name: 'updated_at', type: 'datetime', nullable: true)]
    #[Groups(['file'])]
    private $updatedAt;

    #[ORM\Column(name: 'name', type: 'text', nullable: true)]
    #[Groups(['file'])]
    private $name;

    #[ORM\Column(name: 'extension', type: 'text', nullable: true)]
    #[Groups(['file'])]
    private $extension;

    #[ORM\Column(name: 'mime_type', type: 'text', nullable: true)]
    #[Groups(['file'])]
    private $mimeType;

    #[ORM\Column(name: 'data', type: 'blob', nullable: true)]
    #[Groups(['file_data'])]
    private $data;

    #[ORM\Column(name: 'hash', type: 'string', length: 255, nullable: true)]
    #[Groups(['file'])]
    private $hash;

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
     * Set createdAt
     *
     * @param \DateTime $createdAt
     *
     * @return File
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
     * @return File
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
     * @return File
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
     * Set extension
     *
     * @param string $extension
     *
     * @return File
     */
    public function setExtension($extension)
    {
        $this->extension = $extension;

        return $this;
    }
    /**
     * Get extension
     *
     * @return string
     */
    public function getExtension()
    {
        return $this->extension;
    }
    /**
     * Set mimeType
     *
     * @param string $mimeType
     *
     * @return File
     */
    public function setMimeType($mimeType)
    {
        $this->mimeType = $mimeType;

        return $this;
    }
    /**
     * Get mimeType
     *
     * @return string
     */
    public function getMimeType()
    {
        return $this->mimeType;
    }
    /**
     * Set data
     *
     * @param string $data
     *
     * @return File
     */
    public function setData($data)
    {
        $this->data = $data;

        return $this;
    }
    /**
     * Get data
     *
     * @return string
     */
    public function getData()
    {
        return $this->data;
    }
    /**
     * Set hash
     *
     * @param string $hash
     *
     * @return File
     */
    public function setHash($hash)
    {
        $this->hash = $hash;

        return $this;
    }
    /**
     * Get hash
     *
     * @return string
     */
    public function getHash()
    {
        return $this->hash;
    }
}

