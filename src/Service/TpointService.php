<?php

namespace App\Service;

use App\Entity\Contact;
use App\Entity\ContactGroup;
use App\Entity\Country;
use App\Entity\Employment;
use App\Entity\File;
use App\Entity\Inbox;
use App\Entity\Instrument;
use App\Entity\Program;
use App\Entity\Project;
use App\Entity\State;
use App\Entity\Topic;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;

class TpointService {

    protected $url;
    protected $client;
    protected $em;

    public function __construct(string $url, EntityManagerInterface $em)
    {
        $this->url = $url;
        $this->client = new \GuzzleHttp\Client();
        $this->em = $em;
    }

    public function updateContacts ()
    {

        $result = [
            'created' => [],
            'updated' => [],
        ];

        foreach($this->loadCompanies() as $tpointCompany) {

            $company = $this->em->getRepository(Contact::class)->findOneBy([
                'type' => 'company',
                'tpointId' => $tpointCompany['id'],
            ]);

            if(!$company) {
                $company = new Contact();
                $company
                    ->setType('company')
                    ->setCreatedAt(new \DateTime())
                ;
            }

            $checksum = md5(json_encode($tpointCompany));

            if($checksum === $company->getTpointChecksum()) {
                continue;
            }

            $contactGroups = $this->updateContactGroups($tpointCompany['contactGroups']);

            $company
                ->setUpdatedAt(new \DateTime())
                ->setTpointId($tpointCompany['id'])
                ->setTpointChecksum($checksum)
                ->setIsPublic($tpointCompany['public'])
                ->setCompanyName($tpointCompany['companyName'])
                ->setZipCode($tpointCompany['zipCode'])
                ->setCity($tpointCompany['city'])
                ->setStreet($tpointCompany['address'])
                ->setEmail($tpointCompany['email'])
                ->setPhone($tpointCompany['phone'])
                ->setWebsite($tpointCompany['website'])
                ->setContactGroups(new ArrayCollection($contactGroups))
            ;

            if($company->getId()) {
                $result['updated'][] = $company;
            } else {
                $result['created'][] = $company;
            }

            $this->em->persist($company);

        }

        $this->em->flush();

        foreach($this->loadPersons() as $tpointPerson) {

            $person = $this->em->getRepository(Contact::class)->findOneBy([
                'type' => 'person',
                'tpointId' => $tpointPerson['id'],
            ]);

            if(!$person) {
                $person = new Contact();
                $person
                    ->setType('person')
                    ->setCreatedAt(new \DateTime())
                ;
            }

            $checksum = md5(json_encode($tpointPerson));

            if($checksum === $person->getTpointChecksum()) {
                continue;
            }

            $contactGroups = $this->updateContactGroups($tpointPerson['contactGroups']);

            $person
                ->setUpdatedAt(new \DateTime())
                ->setTpointId($tpointPerson['id'])
                ->setTpointChecksum($checksum)
                ->setIsPublic($tpointPerson['public'])
                ->setFirstName($tpointPerson['forename'])
                ->setLastName($tpointPerson['surname'])
                ->setZipCode($tpointPerson['zipCode'])
                ->setCity($tpointPerson['city'])
                ->setStreet($tpointPerson['address'])
                ->setEmail($tpointPerson['email'])
                ->setPhone($tpointPerson['phone'])
                ->setWebsite($tpointPerson['website'])
                ->setContactGroups(new ArrayCollection($contactGroups))
            ;

            if(str_contains($tpointPerson['vcard'], 'GENDER:M')) {
                $person->setGender('m');
            }

            if(str_contains($tpointPerson['vcard'], 'GENDER:F')) {
                $person->setGender('f');
            }

            if($person->getId()) {
                $result['updated'][] = $person;
            } else {
                $result['created'][] = $person;
            }

            $this->em->persist($person);
            $this->em->flush();

            $employments = $this->em->getRepository(Employment::class)->findBy([
                'employee' => $person,
            ]);;

            foreach($employments as $employment) {
                $this->em->remove($employment);
            }

            $this->em->flush();

            if(!isset($tpointPerson['employments'])) {
                continue;
            }

            foreach($tpointPerson['employments'] as $tpointCompany) {

                $company = $this->em->getRepository(Contact::class)->findOneBy([
                    'type' => 'company',
                    'tpointId' => $tpointCompany['id'],
                ]);

                if(!$company) {
                    continue;
                }

                $employment = $this->em->getRepository(Employment::class)->findOneBy([
                    'company' => $company,
                    'employee' => $person,
                ]);

                if(!$employment) {
                    $emplyoment = new Employment();
                    $emplyoment
                        ->setCreatedAt(new \DateTime())
                    ;
                }

                $emplyoment
                    ->setUpdatedAt(new \DateTime())
                    ->setEmployee($person)
                    ->setCompany($company)
                    ->setRole($tpointPerson['businessFunction'])
                ;

                $this->em->persist($emplyoment);
                $this->em->flush();

            }

            $this->em->refresh($person);

        }

        return $result;

    }

    public function loadPersons ()
    {
        $endpoint = $this->url.'/public/contacts/de.json';
        $response = $this->client->request('GET', $endpoint, []);
        $response = json_decode($response->getBody()->getContents(), true);

        $persons = [];

        foreach($response as $tpointContact) {

            $response = $this->client->request('GET', $tpointContact['json_detail'], []);
            $response = json_decode($response->getBody()->getContents(), true);

            if($response['json-ld']['@type'] !== 'Person') {
                continue;
            }

            $persons[] = $response;


        }

        return $persons;

    }

    public function loadCompanies ()
    {
        $endpoint = $this->url.'/public/contacts/de.json';
        $response = $this->client->request('GET', $endpoint, []);
        $response = json_decode($response->getBody()->getContents(), true);

        $companies = [];

        foreach($response as $tpointContact) {

            $response = $this->client->request('GET', $tpointContact['json_detail'], []);
            $response = json_decode($response->getBody()->getContents(), true);

            if($response['json-ld']['@type'] !== 'Organization') {
                continue;
            }

            $companies[] = $response;


        }

        return $companies;

    }

    public function updateContactGroups ($tpointContactGroups)
    {
        $contactGroups = [];

        foreach($tpointContactGroups as $tpointContactGroup) {

            $contactGroup = $this->em->getRepository(ContactGroup::class)->findOneBy([
                'tpointId' => $tpointContactGroup['group_id'],
            ]);

            if(!$contactGroup) {
                $contactGroup = new ContactGroup();
                $contactGroup
                    ->setCreatedAt(new \DateTime())
                ;
            }

            $checksum = md5(json_encode($tpointContactGroup));

            if($checksum === $contactGroup->getTpointChecksum()) {
                $contactGroups[] = $contactGroup;
                continue;
            }

            $contactGroup
                ->setUpdatedAt(new \DateTime())
                ->setTpointId($tpointContactGroup['group_id'])
                ->setTpointChecksum($checksum)
                ->setIsPublic(true)
                ->setName($tpointContactGroup['project_label'].': '.$tpointContactGroup['group_label'])
                ->setTranslations(['fr' => ['name' => ''], 'it' => ['name' => '']])
            ;

            $this->em->persist($contactGroup);
            $this->em->flush();

            $contactGroups[] = $contactGroup;

        }

        return $contactGroups;
    }

}