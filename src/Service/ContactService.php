<?php

namespace App\Service;

use App\Entity\Contact;
use App\Entity\ContactGroup;
use App\Entity\Country;
use App\Entity\Employment;
use App\Entity\Language;
use App\Entity\State;
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
        $errors = [];
        $validatedFields = [];
        $validatedFieldsEmployments = [];

        foreach($fields as $field) {
            if(!array_key_exists($field, $payload)) {
                $errors[] = [
                    'message' => 'Die übermittelten Daten sind nicht aktuell. Laden Sie die Seite neu und versuchen Sie es erneut.',
                    'field' => $field,
                ];
            }
        }

        if($payload['type'] === 'person') {
            $validatedFields['firstName'] = 'Vorname';
            $validatedFields['lastName'] = 'Nachname';

            $validatedFieldsEmployments['company'] = 'Organisation';
        }

        if($payload['type'] === 'company') {
            $validatedFields['companyName'] = 'Name';

            $validatedFieldsEmployments['employee'] = 'Angestellte(r)';
        }

        foreach($validatedFields as $key => $value) {
            if(!$payload[$key] && $value) {
                $errors[] = [
                    'message' => 'Das Feld "'.$value.'" darf nicht leer stehen.',
                    'field' => $key,
                ];
            }
        }

        if(count($payload['employments']) > 0) {

            foreach($payload['employments'] as $employment) {

                foreach($validatedFieldsEmployments as $key => $value) {
                    if(!$employment[$key] && $value) {
                        $errors[] = [
                            'message' => 'Das Feld "'.$value.'" in der Anstellung darf nicht leer stehen.',
                            'field' => $key,
                        ];
                    }
                }
            }
        }

        return $errors;
    }

    public function validateContactPayload($payload)
    {
        $errors = $this->validateFields($payload, [
            'isPublic',
            'type',
            'companyName',
            'specification',
            'gender',
            'academicTitle',
            'firstName',
            'lastName',
            'street',
            'zipCode',
            'city',
            'country',
            'state',
            'phone',
            'email',
            'website',
            'description',
            'parent',
            'officialEmployment',
            'contactGroups',
            'translations',
        ]);

        return $errors;
    }

    public function createContact($payload)
    {
        $contact = new Contact();

        $contact->setCreatedAt(new \DateTime());

        $contact = $this->applyContactPayload($payload, $contact);

        $this->em->persist($contact);
        $this->em->flush();

        $this->updateEmployments($payload['employments'], $contact, $payload['officialEmployment']);

        return $contact;
    }

    public function updateContact($contact, $payload)
    {
        $contact->setUpdatedAt(new \DateTime());

        $contact = $this->applyContactPayload($payload, $contact);

        $this->em->persist($contact);
        $this->em->flush();

        $this->updateEmployments($payload['employments'], $contact, $payload['officialEmployment']);

        return $contact;
    }

    public function deleteContact($contact)
    {
        $officialEmployment = $contact->getOfficialEmployment();

        if ($officialEmployment) {
            $contact->setOfficialEmployment(null);
            $this->em->persist($contact);
            $this->em->flush();
        }

        foreach($contact->getEmployments() as $emp) {
            $employment = $this->em->getRepository(Employment::class)
                ->find($emp->getId());

            if($employment) {
                $this->em->remove($employment);
            }
        }

        $this->em->flush();

        try {
            $this->em->remove($contact);
            $this->em->flush();
        } catch(\Exception $exception) {
            return false;
        }

        return true;
    }

    public function applyContactPayload($payload, Contact $contact)
    {
        $contact
            ->setIsPublic($payload['isPublic'])
            ->setType($payload['type'])
            ->setCompanyName($payload['companyName'])
            ->setSpecification($payload['specification'])
            ->setGender($payload['gender'])
            ->setAcademicTitle($payload['academicTitle'])
            ->setFirstName($payload['firstName'])
            ->setLastName($payload['lastName'])
            ->setStreet($payload['street'])
            ->setZipCode($payload['zipCode'])
            ->setCity($payload['city'])
            ->setCountry(null)
            ->setState(null)
            ->setLanguage(null)
            ->setPhone($payload['phone'])
            ->setEmail($payload['email'])
            ->setWebsite($payload['website'])
            ->setDescription($payload['description'])
            ->setOfficialEmployment(null)
            ->setContactGroups(new ArrayCollection())
            ->setParent(null)
            ->setTranslations($payload['translations'] ?: [])
        ;

        if($payload['country']) {
            $country = $payload['country'];
            $entity = null;

            if(array_key_exists('id', $country) && $country['id']) {
                $entity = $this->em->getRepository(Country::class)->find($country['id']);
            }
            if(!$entity && array_key_exists('name', $country)) {
                $entity = $this->em->getRepository(Country::class)
                    ->findOneBy(['name' => $country['name']]);
            }
            if($entity) {
                $contact->setCountry($entity);
            }
        }

        if($payload['state']) {
            $state = $payload['state'];
            $entity = null;

            if(array_key_exists('id', $state) && $state['id']) {
                $entity = $this->em->getRepository(State::class)->find($state['id']);
            }
            if(!$entity && array_key_exists('name', $state)) {
                $entity = $this->em->getRepository(State::class)
                    ->findOneBy(['name' => $state['name']]);
            }
            if($entity) {
                $contact->setState($entity);
            }
        }

        if($payload['language']) {
            $language = $payload['language'];
            $entity = null;

            if(array_key_exists('id', $language) && $language['id']) {
                $entity = $this->em->getRepository(Language::class)->find($language['id']);
            }
            if(!$entity && array_key_exists('name', $language)) {
                $entity = $this->em->getRepository(Language::class)
                    ->findOneBy(['name' => $language['name']]);
            }
            if($entity) {
                $contact->setLanguage($entity);
            }
        }

        if($payload['parent']) {
            $parent = $payload['parent'];
            $entity = null;
            if(array_key_exists('id', $parent) && $parent['id']) {
                $entity = $this->em->getRepository(Contact::class)->find($parent['id']);
            }
            if(!$entity && array_key_exists('name', $parent)) {
                $entity = $this->em->getRepository(Contact::class)
                    ->findOneBy(['name' => $parent['name']]);
            }
            if($entity) {
                $contact->setParent($entity);
            }
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
                $contact->addContactGroup($entity);
            }
        }

        return $contact;
    }

    public function updateEmployments($employments, Contact $contact, $officialEmployment) {
        $employmentsOld = $this->em->getRepository(Employment::class)->createQueryBuilder('e')
            ->where('e.company = :contact OR e.employee = :contact')
            ->setParameter('contact', $contact)
            ->getQuery()
            ->getResult()
        ;

        $officialEmploymentEmployeesOld = [];

        foreach($employmentsOld as $employment) {

            if($contact->getType() === 'company') {
                $employee = $this->em->getRepository(Contact::class)->find($employment->getEmployee()->getId());

                if($employee->getOfficialEmployment() && $employee->getOfficialEmployment()->getId() === $employment->getId()) {
                    $officialEmploymentEmployeesOld[] = $employee;

                    $employee->setOfficialEmployment(null);
                }

                $this->em->flush();
            }

            $this->em->remove($employment);
        }

        $this->em->flush();

        foreach($employments as $emp) {
            $employment = new Employment();
            $employment
                ->setCreatedAt(new \DateTime())
                ->setUpdatedAt(new \DateTime())
                ->setCompany(null)
                ->setEmployee(null)
                ->setRole($emp['role'])
                ->setPosition($emp['position'])
                ->setContactGroups(new ArrayCollection())
                ->setTranslations($emp['translations'] ?: [])
            ;

            $employee = null;
            $company = null;

            if($contact->getType() === 'company') {
                $employment
                    ->setCompany($contact);

                $employee = $emp['employee'];
                $entity = null;

                if(array_key_exists('id', $employee) && $employee['id']) {
                    $entity = $this->em->getRepository(Contact::class)->find($employee['id']);
                }
                if(!$entity && array_key_exists('name', $employee)) {
                    $entity = $this->em->getRepository(Contact::class)
                        ->findOneBy(['name' => $employee['name']]);
                }

                if($entity) {
                    $employment
                        ->setEmployee($entity);
                }
            }

            else {
                $employment
                    ->setEmployee($contact);

                $company = $emp['company'];
                $entity = null;

                if(array_key_exists('id', $company) && $company['id']) {
                    $entity = $this->em->getRepository(Contact::class)->find($company['id']);
                }
                if(!$entity && array_key_exists('name', $company)) {
                    $entity = $this->em->getRepository(Contact::class)
                        ->findOneBy(['name' => $company['name']]);
                }

                if($entity) {
                    $employment
                        ->setCompany($entity);
                }
            }

            foreach($employment->getContactGroups() as $contactGroup) {
                $contactGroup->removeContactGroup($employment);
            }

            foreach ($emp['contactGroups'] as $item) {
                $entity = null;
                if (array_key_exists('id', $item) && $item['id']) {
                    $entity = $this->em->getRepository(ContactGroup::class)->find($item['id']);
                }
                if (!$entity && array_key_exists('name', $item)) {
                    $entity = $this->em->getRepository(ContactGroup::class)
                        ->findOneBy(['name' => $item['name']]);
                }
                if ($entity) {
                    $employment->addContactGroup($entity);
                }
            }

            $this->em->persist($employment);
            $this->em->flush();

            if($contact->getType() === 'company') {

                foreach($officialEmploymentEmployeesOld as $employeeOld) {
                    if($employeeOld->getId() === $employee['id']) {
                        $employeeUpdated = $this->em->getRepository(Contact::class)->find($employee['id']);

                        if($employeeUpdated) {
                            $employeeUpdated->setOfficialEmployment($employment);
                        }
                    }
                }

                $this->em->flush();
            }
        }

        $officialEmploymentEntity = null;

        if($officialEmployment) {

            if (array_key_exists('id', $officialEmployment) && $officialEmployment['id']) {
                $officialEmploymentEntity = $this->em->getRepository(Employment::class)->find($officialEmployment['id']);
            }

            if (!$officialEmploymentEntity && array_key_exists('company', $officialEmployment) && $officialEmployment['company']) {

                $officialEmploymentCompany = $this->em->getRepository(Contact::class)->find($officialEmployment['company']['id']);

                if($officialEmploymentCompany) {

                    $officialEmploymentEntity = $this->em->getRepository(Employment::class)
                        ->findOneBy([
                            'employee' => $contact,
                            'company' => $officialEmploymentCompany,
                        ]);
                }
            }
        }

        if($contact->getType() === 'person') {

            if($officialEmploymentEntity) {
                $contact->setOfficialEmployment($officialEmploymentEntity);
                $contact->setCompanyName($officialEmploymentEntity->getCompany()->getName());
            } else {
                $contact->setCompanyName('');
            }

            $this->em->flush();
        }
    }

}