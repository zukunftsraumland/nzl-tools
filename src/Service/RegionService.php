<?php

namespace App\Service;

use App\Entity\City;
use App\Entity\Contact;
use App\Entity\Region;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;

class RegionService {

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

    public function validateRegionPayload($payload)
    {
        if(($errors = $this->validateFields($payload, [
            'isPublic',
            'name',
            'type',
            'url',
            'color',
            'description',
            'cities',
            'contacts',
            'translations',
        ])) !== true) {
            return $errors;
        }

        return true;
    }

    public function createRegion($payload)
    {
        $region = new Region();

        $region->setCreatedAt(new \DateTime());

        $region = $this->applyRegionPayload($payload, $region);

        $this->em->persist($region);
        $this->em->flush();

        return $region;
    }

    public function updateRegion($region, $payload)
    {
        $region->setUpdatedAt(new \DateTime());

        $region = $this->applyRegionPayload($payload, $region);

        $this->em->persist($region);
        $this->em->flush();

        return $region;
    }

    public function deleteRegion($region)
    {
        $this->em->remove($region);
        $this->em->flush();

        return $region;
    }

    public function applyRegionPayload($payload, Region $region)
    {
        $region
            ->setIsPublic($payload['isPublic'])
            ->setType($payload['type'])
            ->setName($payload['name'])
            ->setUrl($payload['url'])
            ->setColor($payload['color'])
            ->setDescription($payload['description'])
            ->setCities(new ArrayCollection())
            ->setContacts(new ArrayCollection())
            ->setTranslations($payload['translations'] ?: [])
        ;

        foreach($payload['cities'] as $item) {
            $entity = null;
            if(array_key_exists('id', $item) && $item['id']) {
                $entity = $this->em->getRepository(City::class)->find($item['id']);
            }
            if(!$entity && array_key_exists('name', $item)) {
                $entity = $this->em->getRepository(City::class)
                    ->findOneBy(['name' => $item['name']]);
            }
            if($entity) {
                $region->addCity($entity);
            }
        }

        foreach($payload['contacts'] as $item) {
            $entity = null;
            if(array_key_exists('id', $item) && $item['id']) {
                $entity = $this->em->getRepository(Contact::class)->find($item['id']);
            }
            /*if(!$entity && array_key_exists('name', $item)) {
                $entity = $this->em->getRepository(Contact::class)
                    ->findOneBy(['name' => $item['name']]);
            }*/
            if($entity) {
                $region->addContact($entity);
            }
        }

        return $region;
    }

}