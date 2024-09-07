<?php

namespace App\Service;

use App\Entity\BusinessSector;
use App\Entity\Country;
use App\Entity\GeographicRegion;
use App\Entity\Instrument;
use App\Entity\Program;
use App\Entity\State;
use App\Entity\Tag;
use App\Entity\Topic;
use App\Util\PvTrans;
use App\Entity\LocalWorkgroup;
use Doctrine\Common\Collections\ArrayCollection;
use App\Entity\Inbox;
use App\Entity\Project;
use Doctrine\ORM\EntityManagerInterface;

class ProjectService {

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

    public function validateProjectPayload($payload)
    {
        if(($errors = $this->validateFields($payload, [
            'title',
            'isPublic',
            'projectCode',
            'keywords',
            'description',
            'topics',
            'tags',
            'geographicRegions',
            'countries',
            'states',
            'programs',
            'instruments',
            'businessSectors',
            'projectCosts',
            'financing',
            'startDate',
            'endDate',
            'dates',
            'contacts',
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

    public function createProject($payload)
    {
        $project = new Project();

        $project->setCreatedAt(new \DateTime());
        $project->setRandom(rand(0, 1000000));

        $project = $this->applyProjectPayload($payload, $project);

        $this->em->persist($project);
        $this->em->flush();

        if(array_key_exists('inboxId', $payload) && $payload['inboxId']) {
            /** @var Inbox $inbox */
            $inbox = $this->em->getRepository(Inbox::class)->find($payload['inboxId']);

            if(array_key_exists('merge', $payload) && $payload['merge']) {
                $inbox->setIsMerged(true);
            } elseif($inbox->getStatus() !== 'deleted') {
                $inbox->setInternalId($project->getId());
                $inbox->setStatus('update');
            }

            $project->setSource($inbox->getSource());

            $this->em->persist($inbox);
        }

        $this->em->persist($project);
        $this->em->flush();

        return $project;
    }

    public function updateProject($project, $payload)
    {
        $project->setUpdatedAt(new \DateTime());

        $project = $this->applyProjectPayload($payload, $project);

        $this->em->persist($project);
        $this->em->flush();

        if(array_key_exists('inboxId', $payload) && $payload['inboxId']) {
            /** @var Inbox $inbox */
            $inbox = $this->em->getRepository(Inbox::class)->find($payload['inboxId']);

            if(array_key_exists('merge', $payload) && $payload['merge']) {
                $inbox->setIsMerged(true);
            } elseif($inbox->getStatus() !== 'deleted') {
                $inbox->setInternalId($project->getId());
                $inbox->setStatus('update');
            }

            $project->setSource($inbox->getSource());

            $this->em->persist($inbox);
        }

        $this->em->persist($project);
        $this->em->flush();

        return $project;
    }

    public function deleteProject($project)
    {
        $inboxItems = $this->em->getRepository(Inbox::class)->findBy(['internalId' => $project->getId()]);

        foreach($inboxItems as $inboxItem) {
            $inboxItem->setIsMerged(true);
            $inboxItem->setMergedAt(new \DateTime());
        }

        $this->em->remove($project);
        $this->em->flush();

        return $project;
    }

    public function applyProjectPayload($payload, Project $project)
    {
        $project
            ->setIsPublic($payload['isPublic'])
            ->setProjectCode($payload['projectCode'])
            ->setTitle($payload['title'])
            ->setKeywords($payload['keywords'])
            ->setDescription($payload['description'])
            ->setProjectCosts($payload['projectCosts'])
            ->setFinancing($payload['financing'] ?: [])
            ->setLinks($payload['links'] ?: [])
            ->setVideos($payload['videos'] ?: [])
            ->setImages($payload['images'] ?: [])
            ->setFiles($payload['files'] ?: [])
            ->setContacts($payload['contacts'] ?: [])
            ->setDates($payload['dates'] ?: [])
            ->setStartDate($payload['startDate'] ? new \DateTime(date('Y-m-d H:i:s', strtotime($payload['startDate']))) : null)
            ->setEndDate($payload['endDate'] ? new \DateTime(date('Y-m-d H:i:s', strtotime($payload['endDate']))) : null)
            ->setCountries(new ArrayCollection())
            ->setStates(new ArrayCollection())
            ->setGeographicRegions(new ArrayCollection())
            ->setTopics(new ArrayCollection())
            ->setTags(new ArrayCollection())
            ->setPrograms(new ArrayCollection())
            ->setInstruments(new ArrayCollection())
            ->setBusinessSectors(new ArrayCollection())
            ->setTranslations($payload['translations'] ?: [])
            ->setLat($payload['lat'] ?? null)
            ->setLng($payload['lng'] ?? null)
            ->setLocalWorkgroup(!empty($payload['localWorkgroup']) ? $this->em->getRepository(LocalWorkgroup::class)->find($payload['localWorkgroup']) : null)
            ->setCooperationProjectAt($payload['cooperationProjectAt'] ?? false)
            ->setCooperationProjectEu($payload['cooperationProjectEu'] ?? false)
            ->setCaseStudy($payload['caseStudy'] ?? false)
            ->setExemplary($payload['exemplary'] ?? '')
            ->setInitialContext($payload['initialContext']?? '')
            ->setInitialContextGoals($payload['initialContextGoals']?? '')
            ->setAdditionalValue($payload['additionalValue']?? '')
            ->setAdditionalValueResult($payload['additionalValueResult']?? '')
            ->setInnovations($payload['innovations']?? '')
            ->setIntegrationYoungCitizen($payload['integrationYoungCitizen']?? '')
            ->setIntegrationFemaleCitizen($payload['integrationFemaleCitizen']?? '')
            ->setIntegrationMinorities($payload['integrationMinorities']?? '')
            ->setLearningExperience($payload['learningExperience']?? '')
            ->setTransferable($payload['transferable']?? '')
            ->setTransferDetails($payload['transferDetails']?? '');

        foreach($payload['countries'] as $item) {
            $entity = null;
            if(array_key_exists('id', $item) && $item['id']) {
                $entity = $this->em->getRepository(Country::class)->find($item['id']);
            }
            if(!$entity && array_key_exists('name', $item)) {
                $entity = $this->em->getRepository(Country::class)
                    ->findOneBy(['name' => $item['name']]);
            }
            if($entity) {
                $project->addCountry($entity);
            }
        }

        foreach($payload['states'] as $item) {
            $entity = null;
            if(array_key_exists('id', $item) && $item['id']) {
                $entity = $this->em->getRepository(State::class)->find($item['id']);
            }
            if(!$entity && array_key_exists('name', $item)) {
                $entity = $this->em->getRepository(State::class)
                    ->findOneBy(['name' => $item['name']]);
            }
            if($entity) {
                $project->addState($entity);
            }
        }

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
                $project->addTopic($entity);
            }
        }

        foreach($payload['tags'] as $item) {
            $entity = null;
            if(array_key_exists('id', $item) && $item['id']) {
                $entity = $this->em->getRepository(Tag::class)->find($item['id']);
            }
            if(!$entity && array_key_exists('name', $item)) {
                $entity = $this->em->getRepository(Tag::class)
                    ->findOneBy(['name' => $item['name']]);
            }
            if($entity) {
                $project->addTag($entity);
            }
        }

        foreach($payload['geographicRegions'] as $item) {
            $entity = null;
            if(array_key_exists('id', $item) && $item['id']) {
                $entity = $this->em->getRepository(GeographicRegion::class)->find($item['id']);
            }
            if(!$entity && array_key_exists('name', $item)) {
                $entity = $this->em->getRepository(GeographicRegion::class)
                    ->findOneBy(['name' => $item['name']]);
            }
            if($entity) {
                $project->addGeographicRegion($entity);
            }
        }

        foreach($payload['programs'] as $item) {
            $entity = null;
            if(array_key_exists('id', $item) && $item['id']) {
                $entity = $this->em->getRepository(Program::class)->find($item['id']);
            }
            if(!$entity && array_key_exists('name', $item)) {
                $entity = $this->em->getRepository(Program::class)
                    ->findOneBy(['name' => $item['name']]);
            }
            if($entity) {
                $project->addProgram($entity);
            }
        }

        foreach($payload['instruments'] as $item) {
            $entity = null;
            if(array_key_exists('id', $item) && $item['id']) {
                $entity = $this->em->getRepository(Instrument::class)->find($item['id']);
            }
            if(!$entity && array_key_exists('name', $item)) {
                $entity = $this->em->getRepository(Instrument::class)
                    ->findOneBy(['name' => $item['name']]);
            }
            if($entity) {
                $project->addInstrument($entity);
            }
        }

        foreach($payload['businessSectors'] as $item) {
            $entity = null;
            if(array_key_exists('id', $item) && $item['id']) {
                $entity = $this->em->getRepository(BusinessSector::class)->find($item['id']);
            }
            if(!$entity && array_key_exists('name', $item)) {
                $entity = $this->em->getRepository(BusinessSector::class)
                    ->findOneBy(['name' => $item['name']]);
            }
            if($entity) {
                $project->addBusinessSector($entity);
            }
        }

        $project->setSearchIndex($this->buildSearchIndex($project));

        return $project;
    }

    public function buildSearchIndex(Project $project): string
    {

        $searchIndex = [];

        $searchIndex[] = $project->getProjectCode();

        foreach(['de', 'fr', 'it'] as $locale) {

            $searchIndex[] = PvTrans::translate($project, 'title', $locale);
            $searchIndex[] = PvTrans::translate($project, 'description', $locale);
            $searchIndex[] = html_entity_decode(strip_tags(PvTrans::translate($project, 'description', $locale)));

            foreach(PvTrans::translate($project, 'contacts', $locale) as $contact) {
                $searchIndex[] = implode(', ', array_filter($contact));
            }

            foreach($project->getTopics() as $e) {
                $searchIndex[] = PvTrans::translate($e, 'name', $locale);
            }

            foreach($project->getPrograms() as $e) {
                $searchIndex[] = PvTrans::translate($e, 'longName', $locale).' ('.PvTrans::translate($e, 'name', $locale).')';
            }

            foreach($project->getStates() as $e) {
                $searchIndex[] = PvTrans::translate($e, 'name', $locale);
            }

            foreach($project->getInstruments() as $e) {
                $searchIndex[] = PvTrans::translate($e, 'name', $locale);
            }

        }

        return implode(PHP_EOL, array_unique(array_filter($searchIndex)));

    }

}