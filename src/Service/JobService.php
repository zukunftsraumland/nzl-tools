<?php

namespace App\Service;

use App\Entity\Location;
use App\Entity\Stint;
use Doctrine\Common\Collections\ArrayCollection;
use App\Entity\Job;
use Doctrine\ORM\EntityManagerInterface;

class JobService {

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

    public function validateJobPayload($payload)
    {
        if(($errors = $this->validateFields($payload, [
            'isPublic',
            'position',
            'name',
            'description',
            'employer',
            'location',
            'contact',
            'applicationDeadline',
            'locations',
            'links',
            'files',
            'translations',
        ])) !== true) {
            return $errors;
        }

        return true;
    }

    public function createJob($payload)
    {
        $job = new Job();

        $job->setCreatedAt(new \DateTime());

        $job = $this->applyJobPayload($payload, $job);

        $this->em->persist($job);
        $this->em->flush();

        return $job;
    }

    public function updateJob($job, $payload)
    {
        $job->setUpdatedAt(new \DateTime());

        $job = $this->applyJobPayload($payload, $job);

        $this->em->persist($job);
        $this->em->flush();

        return $job;
    }

    public function deleteJob($job)
    {
        $this->em->remove($job);
        $this->em->flush();

        return $job;
    }

    public function applyJobPayload($payload, Job $job)
    {
        $job
            ->setIsPublic($payload['isPublic'])
            ->setPosition($payload['position'])
            ->setName($payload['name'])
            ->setDescription($payload['description'])
            ->setEmployer($payload['employer'])
            ->setLocation($payload['location'])
            ->setContact($payload['contact'])
            ->setApplicationDeadline($payload['applicationDeadline'] ? new \DateTime(date('Y-m-d H:i:s', strtotime($payload['applicationDeadline']))) : null)
            ->setLinks($payload['links'] ?: [])
            ->setFiles($payload['files'] ?: [])
            ->setLocations(new ArrayCollection())
            ->setStints(new ArrayCollection())
            ->setTranslations($payload['translations'] ?: [])
        ;

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
                $job->addLocation($entity);
            }
        }

        foreach($payload['stints'] as $item) {
            $entity = null;
            if(array_key_exists('id', $item) && $item['id']) {
                $entity = $this->em->getRepository(Stint::class)->find($item['id']);
            }
            if(!$entity && array_key_exists('name', $item)) {
                $entity = $this->em->getRepository(Stint::class)
                    ->findOneBy(['name' => $item['name']]);
            }
            if($entity) {
                $job->addStint($entity);
            }
        }

        return $job;
    }

}