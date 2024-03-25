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
use App\Entity\Region;
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

        $companies = $this->em->getRepository(Contact::class)->findBy(
            ['type' => 'company']
        );

        $tpointCompanies = $this->loadCompanies();

        $tpointCompaniesIds = array_map(function($company) {
            return $company['id'];
        }, $tpointCompanies);

        $oldCompanies = [];

        foreach($companies as $company) {
            if(!in_array($company->getTpointId(), $tpointCompaniesIds)) {
                $oldCompanies[] = $company;
            }
        }

        foreach($oldCompanies as $company) {

            $qb = $this->em->createQueryBuilder();

            $qb
                ->select('r')
                ->from(Region::class, 'r')
                ->andWhere(':contact MEMBER OF r.contacts')
                ->setParameter('contact', $company)
            ;

            $regions = $qb->getQuery()->getResult();

            if(count($regions)) {
                foreach($regions as $region) {
                    $region->getContacts()->removeElement($company);
                }
            }

            $this->em->flush();

            $this->em->remove($company);
        }

        foreach($tpointCompanies as $tpointCompany) {

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

            if($company->getTpointUid()) {
                if($checksum === $company->getTpointChecksum()) {
                    continue;
                }
            }

            $company
                ->setUpdatedAt(new \DateTime())
                ->setTpointId($tpointCompany['id'])
                ->setTpointChecksum($checksum)
                ->setTpointUid($tpointCompany['vCardUid'])
                ->setIsPublic($tpointCompany['public'])
                ->setCompanyName($tpointCompany['companyName'])
                ->setZipCode($tpointCompany['zipCode'])
                ->setCity($tpointCompany['city'])
                ->setStreet($tpointCompany['address'])
                ->setEmail($tpointCompany['email'])
                ->setPhone($tpointCompany['phone'])
                ->setWebsite($tpointCompany['website'])
                ->setContactGroups(new ArrayCollection())
            ;

            if($company->getId()) {
                $result['updated'][] = $company;
            } else {
                $result['created'][] = $company;
            }

            $this->em->persist($company);

        }

        $this->em->flush();

        $persons = $this->em->getRepository(Contact::class)->findBy(
            ['type' => 'person']
        );

        $tpointPersons = $this->loadPersons();

        $tpointPersonsIds = array_map(function($person) {
            return $person['id'];
        }, $tpointPersons);

        $oldPersons = [];

        foreach($persons as $person) {
            if(!in_array($person->getTpointId(), $tpointPersonsIds)) {
                $oldPersons[] = $person;
            }
        }

        foreach($oldPersons as $person) {

            $qb = $this->em->createQueryBuilder();

            $qb
                ->select('r')
                ->from(Region::class, 'r')
                ->andWhere(':contact MEMBER OF r.contacts')
                ->setParameter('contact', $person)
            ;

            $regions = $qb->getQuery()->getResult();

            if(count($regions)) {
                foreach($regions as $region) {
                    $region->getContacts()->removeElement($person);
                }
            }

            $this->em->flush();
            $this->em->remove($person);
        }

        foreach($tpointPersons as $tpointPerson) {

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

            if($person->getTpointUid()) {
                if($checksum === $person->getTpointChecksum()) {
                    continue;
                }
            }

            $person
                ->setUpdatedAt(new \DateTime())
                ->setTpointId($tpointPerson['id'])
                ->setTpointChecksum($checksum)
                ->setTpointUid($tpointPerson['vCardUid'])
                ->setIsPublic($tpointPerson['public'])
                ->setFirstName($tpointPerson['forename'])
                ->setLastName($tpointPerson['surname'])
                ->setZipCode($tpointPerson['zipCode'])
                ->setCity($tpointPerson['city'])
                ->setStreet($tpointPerson['address'])
                ->setEmail($tpointPerson['email'])
                ->setPhone($tpointPerson['phone'])
                ->setWebsite($tpointPerson['website'])
                ->setContactGroups(new ArrayCollection())
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

}