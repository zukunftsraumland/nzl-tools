<?php

namespace App\Service;

use App\Entity\ContactGroup;
use App\Entity\Contact;
use App\Entity\Employment;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;

class ContactGroupService {

    protected $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    public function validateFields($payload, $fields = [])
    {
        $errors = [];

        foreach($fields as $field) {
            if(!array_key_exists($field, $payload)) {
                $errors[] = [
                    'message' => 'Die übermittelten Daten sind nicht aktuell. Laden Sie die Seite neu und versuchen Sie es erneut.',
                    'field' => $field,
                ];
            }
        }

        if(!$payload['name']) {
            $errors[] = [
                'message' => 'Das Feld "Bezeichnung" darf nicht leer stehen.',
                'field' => 'name',
            ];
        }

        return $errors;
    }

    public function validateContactGroupPayload($payload)
    {
        $errors = $this->validateFields($payload, [
            'position',
            'isPublic',
            'name',
            'parent',
            'contacts',
            'employments',
            'translations',
        ]);

        return $errors;
    }

    public function createContactGroup($payload)
    {
        $contactGroup = new ContactGroup();

        $contactGroup->setCreatedAt(new \DateTime());

        $contactGroup = $this->applyContactGroupPayload($payload, $contactGroup);

        $this->em->persist($contactGroup);
        $this->em->flush();

        return $contactGroup;
    }

    public function updateContactGroup($contactGroup, $payload)
    {
        $contactGroup->setUpdatedAt(new \DateTime());

        $contactGroup = $this->applyContactGroupPayload($payload, $contactGroup);

        $this->em->persist($contactGroup);
        $this->em->flush();

        return $contactGroup;
    }

    public function deleteContactGroup($contactGroup)
    {
        $this->em->remove($contactGroup);
        $this->em->flush();

        return $contactGroup;
    }

    public function applyContactGroupPayload($payload, ContactGroup $contactGroup)
    {
        $contactGroup
            ->setPosition($payload['position'])
            ->setIsPublic($payload['isPublic'])
            ->setName($payload['name'])
            ->setParent(null)
            ->setEmployments(new ArrayCollection())
            ->setTranslations($payload['translations'] ?: [])
        ;

        if($payload['parent']) {
            $parent = $payload['parent'];
            $entity = null;
            if(array_key_exists('id', $parent) && $parent['id']) {
                $entity = $this->em->getRepository(ContactGroup::class)->find($parent['id']);
            }
            if(!$entity && array_key_exists('name', $parent)) {
                $entity = $this->em->getRepository(ContactGroup::class)
                    ->findOneBy(['name' => $parent['name']]);
            }
            if($entity) {
                $contactGroup->setParent($entity);
            }
        }

        foreach($contactGroup->getContacts() as $contact) {
            $contact->removeContactGroup($contactGroup);
        }
        foreach($payload['contacts'] as $item) {
            $entity = null;
            if(array_key_exists('id', $item) && $item['id']) {
                $entity = $this->em->getRepository(Contact::class)->find($item['id']);
            }
            if(!$entity && array_key_exists('name', $item)) {
                $entity = $this->em->getRepository(Contact::class)
                    ->findOneBy(['name' => $item['name']]);
            }
            if($entity) {
                $contactGroup->addContact($entity);
            }
        }

        foreach($contactGroup->getEmployments() as $employment) {
            $employment->removeContactGroup($contactGroup);
        }

        foreach($payload['employments'] as $item) {
            $entity = null;
            if(array_key_exists('id', $item) && $item['id']) {
                $entity = $this->em->getRepository(Employment::class)->find($item['id']);
            }
            if(!$entity && array_key_exists('employee', $item) && $item['employee'] && array_key_exists('company', $item) && $item['company']) {

                $employee = $this->em->getRepository(Contact::class)
                    ->find($item['employee']['id']);

                $company = $this->em->getRepository(Contact::class)
                    ->find($item['company']['id']);

                $entity = $this->em->getRepository(Employment::class)
                    ->findOneBy([
                        'employee' => $employee,
                        'company' => $company
                    ]);
            }
            if($entity) {
                $contactGroup->addEmployment($entity);
            }
        }

        return $contactGroup;
    }

}