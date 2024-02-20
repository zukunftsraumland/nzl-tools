<?php

namespace App\Service;

use App\Entity\Language;
use App\Entity\Location;
use App\Entity\EducationType;
use Doctrine\Common\Collections\ArrayCollection;
use App\Entity\Education;
use Doctrine\ORM\EntityManagerInterface;

class EducationService {

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

    public function validateEducationPayload($payload)
    {
        if(($errors = $this->validateFields($payload, [
            'isPublic',
            'name',
            'description',
            'organizer',
            'location',
            'contact',
            'text',
            'educationTypes',
            'languages',
            'locations',
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

    public function createEducation($payload)
    {
        $education = new Education();

        $education->setCreatedAt(new \DateTime());

        $education = $this->applyEducationPayload($payload, $education);

        $this->em->persist($education);
        $this->em->flush();

        return $education;
    }

    public function updateEducation($education, $payload)
    {
        $education->setUpdatedAt(new \DateTime());

        $education = $this->applyEducationPayload($payload, $education);

        $this->em->persist($education);
        $this->em->flush();

        return $education;
    }

    public function deleteEducation($education)
    {
        $this->em->remove($education);
        $this->em->flush();

        return $education;
    }

    public function applyEducationPayload($payload, Education $education)
    {
        $education
            ->setIsPublic($payload['isPublic'])
            ->setName($payload['name'])
            ->setDescription($payload['description'])
            ->setText($payload['text'])
            ->setOrganizer($payload['organizer'])
            ->setLocation($payload['location'])
            ->setContact($payload['contact'])
            ->setLinks($payload['links'] ?: [])
            ->setVideos($payload['videos'] ?: [])
            ->setImages($payload['images'] ?: [])
            ->setFiles($payload['files'] ?: [])
            ->setEducationTypes(new ArrayCollection())
            ->setLanguages(new ArrayCollection())
            ->setLocations(new ArrayCollection())
            ->setTranslations($payload['translations'] ?: [])
        ;

        foreach($payload['educationTypes'] as $item) {
            $entity = null;
            if(array_key_exists('id', $item) && $item['id']) {
                $entity = $this->em->getRepository(EducationType::class)->find($item['id']);
            }
            if(!$entity && array_key_exists('name', $item)) {
                $entity = $this->em->getRepository(EducationType::class)
                    ->findOneBy(['name' => $item['name']]);
            }
            if($entity) {
                $education->addEducationType($entity);
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
                $education->addLanguage($entity);
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
                $education->addLocation($entity);
            }
        }

        return $education;
    }

}