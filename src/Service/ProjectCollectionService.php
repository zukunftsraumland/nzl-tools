<?php

namespace App\Service;

use Doctrine\Common\Collections\ArrayCollection;
use App\Entity\Event;
use App\Entity\EventCollection;
use App\Entity\Inbox;
use App\Entity\InteractiveGraphic;
use App\Entity\Project;
use App\Entity\ProjectCollection;
use Doctrine\ORM\EntityManagerInterface;

class ProjectCollectionService {

    protected $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    public function validateFields($payload, $fields = [])
    {
        foreach($fields as $field) {
            if(!array_key_exists($field, $payload)) {
                return [
                    [
                        'field' => $field,
                    ]
                ];
            }
        }

        return true;
    }

    public function validateProjectCollectionPayload($payload)
    {
        if(($errors = $this->validateFields($payload, [
            'title',
            'isDynamic',
            'description',
            'selection',
            'filters',
        ])) !== true) {
            return $errors;
        }

        return true;
    }

    public function createProjectCollection($payload)
    {
        $projectCollection = new ProjectCollection();

        $projectCollection->setCreatedAt(new \DateTime());

        $projectCollection = $this->applyProjectCollectionPayload($payload, $projectCollection);

        $this->em->persist($projectCollection);
        $this->em->flush();

        return $projectCollection;
    }

    public function updateProjectCollection($projectCollection, $payload)
    {
        $projectCollection->setUpdatedAt(new \DateTime());

        $projectCollection = $this->applyProjectCollectionPayload($payload, $projectCollection);

        $this->em->persist($projectCollection);
        $this->em->flush();

        return $projectCollection;
    }

    public function deleteProjectCollection($projectCollection)
    {
        $this->em->remove($projectCollection);
        $this->em->flush();

        return $projectCollection;
    }

    public function applyProjectCollectionPayload($payload, ProjectCollection $projectCollection)
    {
        $projectCollection
            ->setIsDynamic($payload['isDynamic'])
            ->setTitle($payload['title'])
            ->setDescription($payload['description'])
            ->setSelection($payload['selection'] ?: [])
            ->setFilters($payload['filters'] ?: [])
        ;

        return $projectCollection;
    }

}