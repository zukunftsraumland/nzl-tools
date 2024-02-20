<?php

namespace App\Service;

use App\Entity\Language;
use App\Entity\Location;
use App\Entity\Topic;
use Doctrine\Common\Collections\ArrayCollection;
use App\Entity\Event;
use Doctrine\ORM\EntityManagerInterface;

class EventService {

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

    public function validateEventPayload($payload)
    {
        if(($errors = $this->validateFields($payload, [
            'isPublic',
            'isPromotedDE',
            'isPromotedFR',
            'isPromotedIT',
            'title',
            'description',
            'organizer',
            'location',
            'contact',
            'text',
            'type',
            'color',
            'startDate',
            'endDate',
            'topics',
            'languages',
            'locations',
            'programs',
            'registration',
            'links',
            'videos',
            'images',
            'files',
            'translations',
        ])) !== true) {
            return $errors;
        }

        return true;
    }

    public function createEvent($payload)
    {
        $event = new Event();

        $event->setCreatedAt(new \DateTime());

        $event = $this->applyEventPayload($payload, $event);

        $this->em->persist($event);
        $this->em->flush();

        return $event;
    }

    public function updateEvent($event, $payload)
    {
        $event->setUpdatedAt(new \DateTime());

        $event = $this->applyEventPayload($payload, $event);

        $this->em->persist($event);
        $this->em->flush();

        return $event;
    }

    public function deleteEvent($event)
    {
        $this->em->remove($event);
        $this->em->flush();

        return $event;
    }

    public function applyEventPayload($payload, Event $event)
    {
        $event
            ->setIsPublic($payload['isPublic'])
            ->setIsPromotedDE($payload['isPromotedDE'])
            ->setIsPromotedFR($payload['isPromotedFR'])
            ->setIsPromotedIT($payload['isPromotedIT'])
            ->setTitle($payload['title'])
            ->setDescription($payload['description'])
            ->setText($payload['text'])
            ->setOrganizer($payload['organizer'])
            ->setLocation($payload['location'])
            ->setContact($payload['contact'])
            ->setType($payload['type'])
            ->setColor($payload['color'])
            ->setStartDate($payload['startDate'] ? new \DateTime(date('Y-m-d H:i:s', strtotime($payload['startDate']))) : null)
            ->setEndDate($payload['endDate'] ? new \DateTime(date('Y-m-d H:i:s', strtotime($payload['endDate']))) : null)
            ->setRegistration($payload['registration'])
            ->setLinks($payload['links'] ?: [])
            ->setVideos($payload['videos'] ?: [])
            ->setImages($payload['images'] ?: [])
            ->setFiles($payload['files'] ?: [])
            ->setPrograms($payload['programs'] ?: [])
            ->setTopics(new ArrayCollection())
            ->setLanguages(new ArrayCollection())
            ->setLocations(new ArrayCollection())
            ->setTranslations($payload['translations'] ?: [])
        ;

        foreach($payload['topics'] as $item) {
            $entity = null;
            if(array_key_exists('id', $item) && $item['id']) {
                $entity = $this->em->getRepository(Topic::class)->find($item['id']);
            }
            if(!$entity && array_key_exists('name', $item)) {
                $entity = $this->em->getRepository(Topic::class)
                    ->findOneBy(['name' => $item['name']]);
            }
            if($entity) {
                $event->addTopic($entity);
            }
        }

        foreach($payload['languages'] as $item) {
            $entity = null;
            if(array_key_exists('id', $item) && $item['id']) {
                $entity = $this->em->getRepository(Language::class)->find($item['id']);
            }
            if(!$entity && array_key_exists('name', $item)) {
                $entity = $this->em->getRepository(Language::class)
                    ->findOneBy(['name' => $item['name']]);
            }
            if($entity) {
                $event->addLanguage($entity);
            }
        }

        foreach($payload['locations'] as $item) {
            $entity = null;
            if(array_key_exists('id', $item) && $item['id']) {
                $entity = $this->em->getRepository(Location::class)->find($item['id']);
            }
            if(!$entity && array_key_exists('name', $item)) {
                $entity = $this->em->getRepository(Location::class)
                    ->findOneBy(['name' => $item['name']]);
            }
            if($entity) {
                $event->addLocation($entity);
            }
        }

        return $event;
    }

}