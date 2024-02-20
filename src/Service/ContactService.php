<?php

namespace App\Service;

use App\Entity\Contact;
use App\Entity\ContactGroup;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;

class ContactService {

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

    public function validateContactPayload($payload)
    {
        if(($errors = $this->validateFields($payload, [
            'isPublic',
            'companyName',
            'gender',
            'academicTitle',
            'firstName',
            'lastName',
            'description',
            'zipCode',
            'city',
            'street',
            'phone',
            'email',
            'website',
            'translations',
            'employments',
            'employees',
            'contactGroups',
        ])) !== true) {
            return $errors;
        }

        return true;
    }

    public function createContact($payload)
    {
        $contact = new Contact();

        $contact->setCreatedAt(new \DateTime());

        $contact = $this->applyContactPayload($payload, $contact);

        $this->em->persist($contact);
        $this->em->flush();

        return $contact;
    }

    public function updateContact($contact, $payload)
    {
        $contact->setUpdatedAt(new \DateTime());

        $contact = $this->applyContactPayload($payload, $contact);

        $this->em->persist($contact);
        $this->em->flush();

        return $contact;
    }

    public function deleteContact($contact)
    {
        $this->em->remove($contact);
        $this->em->flush();

        return $contact;
    }

    public function applyContactPayload($payload, Contact $contact)
    {
        $contact
            ->setIsPublic($payload['isPublic'])
            ->setCompanyName($payload['companyName'])
            ->setGender($payload['gender'])
            ->setAcademicTitle($payload['academicTitle'])
            ->setFirstName($payload['firstName'])
            ->setLastName($payload['lastName'])
            ->setDescription($payload['description'])
            ->setZipCode($payload['zipCode'])
            ->setCity($payload['city'])
            ->setStreet($payload['street'])
            ->setPhone($payload['phone'])
            ->setEmail($payload['email'])
            ->setWebsite($payload['website'])
            ->setTranslations($payload['translations'] ?: [])
            ->setEmployments(new ArrayCollection())
            ->setEmployees(new ArrayCollection())
            ->setContactGroups(new ArrayCollection())
        ;

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
                $contact->addContactGroup($entity);
            }
        }

        return $contact;
    }

}