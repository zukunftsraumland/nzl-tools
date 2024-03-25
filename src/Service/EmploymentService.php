<?php

namespace App\Service;

use App\Entity\ContactGroup;
use App\Entity\Employment;
use App\Entity\Contact;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;

class EmploymentService {

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

    public function validateEmploymentPayload($payload)
    {
        if(($errors = $this->validateFields($payload, [
            'translations',
            'company',
            'employee',
            'contactGroups',
            'position',
            'role',
        ])) !== true) {
            return $errors;
        }

        return true;
    }

    public function createEmployment($payload)
    {
        $employment = new Employment();

        $employment->setCreatedAt(new \DateTime());

        $employment = $this->applyEmploymentPayload($payload, $employment);

        $this->em->persist($employment);
        $this->em->flush();

        return $employment;
    }

    public function updateEmployment($employment, $payload)
    {
        $employment->setUpdatedAt(new \DateTime());

        $employment = $this->applyEmploymentPayload($payload, $employment);

        $this->em->persist($employment);
        $this->em->flush();

        return $employment;
    }

    public function deleteEmployment($employment)
    {
        $this->em->remove($employment);
        $this->em->flush();

        return $employment;
    }

    public function applyEmploymentPayload($payload, Employment $employment)
    {
        $employment
            ->setCompany(null)
            ->setEmployee(null)
            ->setContactGroups(new ArrayCollection())
            ->setPosition($payload['position'])
            ->setRole($payload['role'])
            ->setTranslations($payload['translations'] ?: [])
        ;

        if($payload['company']) {
            $company = $payload['company'];
            $entity = null;

            if(array_key_exists('id', $company) && $company['id']) {
                $entity = $this->em->getRepository(Contact::class)->find($company['id']);
            }
            if(!$entity && array_key_exists('name', $company)) {
                $entity = $this->em->getRepository(Contact::class)
                    ->findOneBy(['name' => $company['name']]);
            }
            if($entity) {
                $employment->setCompany($entity);
            }
        }

        if($payload['employee']) {
            $employee = $payload['employee'];
            $entity = null;

            if(array_key_exists('id', $employee) && $employee['id']) {
                $entity = $this->em->getRepository(Contact::class)->find($employee['id']);
            }
            if(!$entity && array_key_exists('name', $employee)) {
                $entity = $this->em->getRepository(Contact::class)
                    ->findOneBy(['name' => $employee['name']]);
            }
            if($entity) {
                $employment->setEmployee($entity);
            }
        }

        foreach($employment->getContactGroups() as $contactGroup) {
            $contactGroup->removeContactGroup($employment);
        }
        foreach($payload['contactGroups'] as $item) {
            $entity = null;
            if(array_key_exists('id', $item) && $item['id']) {
                $entity = $this->em->getRepository(ContactGroup::class)->find($item['id']);
            }
            if(!$entity && array_key_exists('name', $item)) {
                $entity = $this->em->getRepository(ContactGroup::class)
                    ->findOneBy(['name' => $item['name']]);
            }
            if($entity) {
                $employment->addContactGroup($entity);
            }
        }

        return $employment;
    }

}