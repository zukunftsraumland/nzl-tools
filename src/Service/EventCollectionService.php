<?php

namespace App\Service;

use App\Entity\EventCollection;
use Doctrine\ORM\EntityManagerInterface;

class EventCollectionService {

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

    public function validateEventCollectionPayload($payload)
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

    public function createEventCollection($payload)
    {
        $eventCollection = new EventCollection();

        $eventCollection->setCreatedAt(new \DateTime());

        $eventCollection = $this->applyEventCollectionPayload($payload, $eventCollection);

        $this->em->persist($eventCollection);
        $this->em->flush();

        return $eventCollection;
    }

    public function updateEventCollection($eventCollection, $payload)
    {
        $eventCollection->setUpdatedAt(new \DateTime());

        $eventCollection = $this->applyEventCollectionPayload($payload, $eventCollection);

        $this->em->persist($eventCollection);
        $this->em->flush();

        return $eventCollection;
    }

    public function deleteEventCollection($eventCollection)
    {
        $this->em->remove($eventCollection);
        $this->em->flush();

        return $eventCollection;
    }

    public function applyEventCollectionPayload($payload, EventCollection $eventCollection)
    {
        $eventCollection
            ->setIsDynamic($payload['isDynamic'])
            ->setTitle($payload['title'])
            ->setDescription($payload['description'])
            ->setSelection($payload['selection'] ?: [])
            ->setFilters($payload['filters'] ?: [])
        ;

        return $eventCollection;
    }

}