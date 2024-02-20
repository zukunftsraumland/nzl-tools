<?php

namespace App\Service;

use App\Entity\Country;
use App\Entity\File;
use App\Entity\Inbox;
use App\Entity\Instrument;
use App\Entity\Program;
use App\Entity\Project;
use App\Entity\State;
use App\Entity\Tag;
use App\Entity\Topic;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;

class ChmosService {

    protected $url;
    protected $certificate;
    protected $client;
    protected $em;
    protected $projectService;

    public function __construct(string $url, string $certificate, EntityManagerInterface $em, ProjectService $projectService)
    {
        $this->url = $url;
        $this->certificate = $certificate;
        $this->client = new \GuzzleHttp\Client();
        $this->em = $em;
        $this->projectService = $projectService;
    }

    public function authenticatedRequest ($endpoint)
    {
        return $this->client->request('GET', $endpoint, [
            'cert' => $this->certificate
        ]);
    }

    public function performUpdate ($since = null, $till = null, $autoMerge = false)
    {
        $since = $since ?: new \DateTime('-3 years');
        $endpoint = $this->url.'/project/1/list?updatedSince='.$since->format('d.m.Y');
        $response = $this->client->request('GET', $endpoint, [
            'cert' => $this->certificate
        ]);

        $content = $response->getBody()->getContents();

        if($response->getStatusCode() !== 200 || !$content) {
            throw new \Exception('Could not load: '.$endpoint);
        }

        $chmosProjects = json_decode($content, true);

        if($till) {

            $endpoint = $this->url.'/project/1/list?updatedSince='.$till->format('d.m.Y');
            $response = $this->client->request('GET', $endpoint, [
                'cert' => $this->certificate
            ]);

            $tillContent = $response->getBody()->getContents();

            if($response->getStatusCode() !== 200 || !$tillContent) {
                throw new \Exception('Could not load: '.$endpoint);
            }

            $chmosProjectsTill = json_decode($tillContent, true);

            $chmosProjects = array_filter($chmosProjects, function ($chmosProject) use ($chmosProjectsTill) {
                $count = count(array_filter($chmosProjectsTill, function ($chmosProjectTill) use ($chmosProject) {
                    return $chmosProjectTill['id'] === $chmosProject['id'];
                }));
                return 0 === $count;
            });

        }

        $inboxItems = [];

        foreach($chmosProjects as $chmosProject) {

            try {
                $inboxItems[] = $this->performProjectUpdate($chmosProject['id'], $autoMerge);
            } catch (\Exception $exception) {
                echo ($exception->getMessage().PHP_EOL);
                // delete project
            }
        }

        return $inboxItems;
    }

    public function performProjectUpdate ($id, $autoMerge = false)
    {
        $endpoint = $this->url.'/project/1/'.$id;
        $response = $this->client->request('GET', $endpoint, [
            'cert' => $this->certificate
        ]);

        $content = $response->getBody()->getContents();

        $chmosProject = json_decode($content, true);

        if($response->getStatusCode() !== 200 || !$content || !$chmosProject) {

            if($project = $this->identifyProject($id)) {

                $inboxItem = new Inbox();

                $existing = $this->em->getRepository(Inbox::class)->findOneBy([
                    'internalId' => $project->getId(),
                    'type' => 'project',
                ]);

                $inboxItem = $existing ?: $inboxItem;

                $inboxItem
                    ->setCreatedAt(new \DateTime())
                    ->setTitle($project->getTitle())
                    ->setType('project')
                    ->setStatus('deleted')
                    ->setSource('chmos')
                    ->setIsMerged(false)
                    ->setData([])
                    ->setNormalizedData([])
                    ->setInternalId($project->getId())
                ;

                if(!$inboxItem->getTitle() && count($project->getTranslations())) {
                    $t = array_values($project->getTranslations());
                    $inboxItem->setTitle(array_shift($t)['title']);
                }

                if($existing) {
                    $inboxItem->setCreatedAt($existing->getCreatedAt());
                }

                $this->em->persist($inboxItem);
                $this->em->flush();

                return;
            }

            throw new \Exception('Could not load: '.$endpoint);

        }

        $normalizedProject = $this->normalizeProject($chmosProject);

        $inboxItem = new Inbox();

        $inboxItem
            ->setForeignId($normalizedProject['id'])
            ->setCreatedAt(new \DateTime())
            ->setData($chmosProject)
            ->setNormalizedData($normalizedProject)
            ->setStatus($normalizedProject['status'])
            ->setType('project')
            ->setIsMerged(false)
            ->setSource('chmos')
            ->setTitle($normalizedProject['title'])
        ;

        $existing = $this->em->getRepository(Inbox::class)->findOneBy([
            'foreignId' => $inboxItem->getForeignId(),
            'type' => 'project',
            'isMerged' => false,
        ]);

        if($existing) {
            if(json_encode($inboxItem->getData()) !== json_encode($existing->getData()) ||
                json_encode($inboxItem->getNormalizedData()) !== json_encode($existing->getNormalizedData())) {
                $existing
                    ->setTitle($normalizedProject['title'])
                    ->setData($chmosProject)
                    ->setNormalizedData($normalizedProject)
                    ->setUpdatedAt(new \DateTime())
                ;
            }
            $inboxItem = $existing;
        }

        if($project = $this->identifyProject($normalizedProject['projectCode'])) {
            $inboxItem->setStatus('update');
            $inboxItem->setInternalId($project->getId());
        }

        $this->performTopicUpdate($normalizedProject);
        $this->performInstrumentUpdate($normalizedProject);
        $this->performProgramUpdate($normalizedProject);
        $this->performCountryUpdate($normalizedProject);
        $this->performStateUpdate($normalizedProject);

        $normalizedProject = $this->applyTags($normalizedProject);

        $inboxItem->setNormalizedData($normalizedProject);

        if($autoMerge) {
            if($project) {
                $this->projectService->updateProject($project, $normalizedProject);
            } else {
                $this->projectService->createProject($normalizedProject);
            }

            $inboxItem->setIsMerged(true);
            $inboxItem->setMergedAt(new \DateTime());
        }

        $this->em->persist($inboxItem);
        $this->em->flush();
        $this->em->clear();

        return $inboxItem;

    }

    public function performProjectPatch ($project, $properties = [])
    {
        if(!$project->getProjectCode()) {
            return false;
        }

        $endpoint = $this->url.'/project/1/'.$project->getProjectCode();
        $response = $this->client->request('GET', $endpoint, [
            'cert' => $this->certificate
        ]);

        $content = $response->getBody()->getContents();

        $chmosProject = json_decode($content, true);

        if($response->getStatusCode() !== 200 || !$content || !$chmosProject) {

            return false;

        }

        $normalizedProject = $this->normalizeProject($chmosProject);

        if(in_array('tags', $properties)) {
            $normalizedProject = $this->applyTags($normalizedProject);

            $project->setTags(new ArrayCollection());

            foreach($normalizedProject['tags'] as $tag) {

                $tag = $this->em->getRepository(Tag::class)->find($tag['id']);

                if($tag) {
                    $project->setUpdatedAt(new \DateTime());
                    $project->addTag($tag);
                }

            }
        }

        if(in_array('links', $properties)) {

            if(count($normalizedProject['links'])) {

                if(!count($project->getLinks())) {
                    $project->setUpdatedAt(new \DateTime());
                    $project->setLinks($normalizedProject['links']);
                }

            }

        }

        if(in_array('lat', $properties)) {

            if($normalizedProject['lat']) {

                $project->setUpdatedAt(new \DateTime());
                $project->setLat($normalizedProject['lat']);

            }

        }

        if(in_array('lng', $properties)) {

            if($normalizedProject['lng']) {

                $project->setUpdatedAt(new \DateTime());
                $project->setLng($normalizedProject['lng']);

            }

        }

        $this->em->persist($project);
        $this->em->flush();

        return true;

    }

    public function performTopicUpdate($normalizedProject)
    {
        foreach($normalizedProject['topics'] as $topic) {

            if($topic['id']) {
                continue;
            }

            $inboxItem = new Inbox();
            $inboxItem
                ->setCreatedAt(new \DateTime())
                ->setForeignId($topic['foreignId'])
                ->setType('topic')
                ->setData([])
                ->setNormalizedData($normalizedProject['topics'])
                ->setIsMerged(false)
                ->setSource('chmos')
                ->setTitle($topic['name'])
                ->setStatus('new')
            ;

            $existing = $this->em->getRepository(Inbox::class)->findOneBy([
                'foreignId' => $inboxItem->getForeignId(),
                'type' => 'topic',
            ]);

            if($existing) {
                $inboxItem = $existing;
            }

            $this->em->persist($inboxItem);
            $this->em->flush();
        }
    }

    public function performInstrumentUpdate($normalizedProject)
    {
        foreach($normalizedProject['instruments'] as $instrument) {

            if($instrument['id']) {
                continue;
            }

            $inboxItem = new Inbox();
            $inboxItem
                ->setCreatedAt(new \DateTime())
                ->setForeignId($instrument['foreignId'])
                ->setType('instrument')
                ->setData([])
                ->setNormalizedData($normalizedProject['instruments'])
                ->setIsMerged(false)
                ->setSource('chmos')
                ->setTitle($instrument['name'])
                ->setStatus('new')
            ;

            $existing = $this->em->getRepository(Inbox::class)->findOneBy([
                'foreignId' => $inboxItem->getForeignId(),
                'type' => 'instrument',
            ]);

            if($existing) {
                $inboxItem = $existing;
            }

            $this->em->persist($inboxItem);
            $this->em->flush();
        }
    }

    public function performProgramUpdate($normalizedProject)
    {
        foreach($normalizedProject['programs'] as $program) {

            if($program['id']) {
                continue;
            }

            $inboxItem = new Inbox();
            $inboxItem
                ->setCreatedAt(new \DateTime())
                ->setForeignId($program['foreignId'])
                ->setType('program')
                ->setData([])
                ->setNormalizedData($normalizedProject['programs'])
                ->setIsMerged(false)
                ->setSource('chmos')
                ->setTitle($program['name'])
                ->setStatus('new')
            ;

            $existing = $this->em->getRepository(Inbox::class)->findOneBy([
                'foreignId' => $inboxItem->getForeignId(),
                'type' => 'program',
            ]);

            if($existing) {
                $inboxItem = $existing;
            }

            $this->em->persist($inboxItem);
            $this->em->flush();
        }
    }

    public function performCountryUpdate($normalizedProject)
    {
        foreach($normalizedProject['countries'] as $country) {

            if($country['id']) {
                continue;
            }

            $inboxItem = new Inbox();
            $inboxItem
                ->setCreatedAt(new \DateTime())
                ->setForeignId($country['foreignId'])
                ->setType('country')
                ->setData([])
                ->setNormalizedData($normalizedProject['countries'])
                ->setIsMerged(false)
                ->setSource('chmos')
                ->setTitle($country['name'])
                ->setStatus('new')
            ;

            $existing = $this->em->getRepository(Inbox::class)->findOneBy([
                'foreignId' => $inboxItem->getForeignId(),
                'type' => 'country',
            ]);

            if($existing) {
                $inboxItem = $existing;
            }

            $this->em->persist($inboxItem);
            $this->em->flush();
        }
    }

    public function performStateUpdate($normalizedProject)
    {
        foreach($normalizedProject['states'] as $state) {

            if($state['id']) {
                continue;
            }

            $inboxItem = new Inbox();
            $inboxItem
                ->setCreatedAt(new \DateTime())
                ->setForeignId($state['foreignId'])
                ->setType('state')
                ->setData([])
                ->setNormalizedData($normalizedProject['states'])
                ->setIsMerged(false)
                ->setSource('chmos')
                ->setTitle($state['name'])
                ->setStatus('new')
            ;

            $existing = $this->em->getRepository(Inbox::class)->findOneBy([
                'foreignId' => $inboxItem->getForeignId(),
                'type' => 'state',
            ]);

            if($existing) {
                $inboxItem = $existing;
            }

            $this->em->persist($inboxItem);
            $this->em->flush();
        }
    }

    public function normalizeMalformedData($chmosProject)
    {
        switch($chmosProject['instrument']['textDe']) {
            case 'Pilotmassnahmen für die Berggebiete - àfp':
                $chmosProject['programm'] = 'NRP-Pilotmassnahmen Berggebiete';
        }

        return $chmosProject;
    }

    public function normalizeProject($chmosProject)
    {
        $chmosProject = $this->normalizeMalformedData($chmosProject);

        $normalizedProject = [
            'id'                => $chmosProject['id'],
            'isPublic'          => true,
            'status'            => 'new',
            'title'             => $chmosProject['titel'] ?: '',
            'keywords'          => '',
            'description'       => html_entity_decode($chmosProject['beschreibung']) ?: '',
            'attachments'       => $chmosProject['attachments'] ?: [],
            'projectCode'       => $chmosProject['codeKurz'],
            'projectCosts'      => $chmosProject['projektkosten'],
            'financing'         => [],
            'topics'            => [],
            'programs'          => [$this->normalizeProgram($chmosProject['programm'])],
            'instruments'       => [$this->normalizeInstrument($chmosProject['instrument'])],
            'countries'         => [$this->normalizeCountry('Schweiz')],
            'states'            => $this->normalizeStates($chmosProject['kanton']),
            'startDate'         => (new \DateTime($chmosProject['start']))->format(\DateTimeInterface::ATOM),
            'endDate'           => (new \DateTime($chmosProject['ende']))->format(\DateTimeInterface::ATOM),
            'dates'             => [],
            'contacts'          => [],
            'links'             => [],
            'geographicRegions' => [],
            'videos'            => [],
            'images'            => [],
            'files'             => [],
            'translations'      => [],
            'businessSectors'   => [],
            'tags'              => [],
            'lat'               => null,
            'lng'               => null,
        ];

        foreach($chmosProject['attachments'] as $attachment) {

            if(!$attachment['oeffentlich']) {
                continue;
            }

            try {

                $endpoint = $this->url.str_replace('/project', '/project/1', $attachment['url']);
                $response = $this->client->request('GET', $endpoint, [
                    'cert' => $this->certificate
                ]);

                $data = base64_encode($response->getBody()->getContents());
                $hash = md5($data);

                $file = $this->em->getRepository(File::class)->findOneBy([
                    'hash' => $hash,
                ]);

                $extension = 'file';

                switch($attachment['type']) {
                    case 'image/jpeg':
                    case 'image/jpg':
                        $extension = 'jpg';
                        break;
                    case 'image/png':
                        $extension = 'png';
                        break;
                    case 'image/gif':
                        $extension = 'gif';
                        break;
                    case 'application/pdf':
                        $extension = 'pdf';
                        break;
                }

                if(!$file) {
                    $file = new File();
                    $file
                        ->setName($hash.'.'.$extension)
                        ->setCreatedAt(new \DateTime())
                        ->setData($data)
                        ->setHash($hash)
                        ->setMimeType($attachment['type'])
                        ->setExtension($extension)
                    ;

                    $this->em->persist($file);
                    $this->em->flush();
                }

                if(in_array($extension, ['jpg', 'png', 'gif'])) {
                    $normalizedProject['images'][] = [
                        'id' => $file->getId(),
                        'extension' => $file->getExtension(),
                        'mimeType' => $attachment['type'],
                        'copyright' => $attachment['copyright'],
                        'description' => '',
                    ];
                } else {
                    $normalizedProject['files'][] = [
                        'id' => $file->getId(),
                        'extension' => $file->getExtension(),
                        'mimeType' => $attachment['type'],
                        'copyright' => $attachment['copyright'],
                        'description' => '',
                    ];
                }

            } catch (\Exception $exception) {
                //throw $exception;
            }
        }

        foreach(array_keys($chmosProject) as $key) {
            $financialMapping = [
                'finBund' => 'costsFederation',
                'finKanton' => 'costsCanton',
                'kostenEU' => 'costsEU',
            ];
            if($key === 'finKanton' && array_key_exists('finKantonOeff', $chmosProject) && !$chmosProject['finKantonOeff']) {
                continue;
            }
            if(in_array($key, array_keys($financialMapping))) {
                if($chmosProject[$key]) {
                    $normalizedProject['financing'][] = [
                        'id'        => $financialMapping[$key] ?: null,
                        'foreignId' => $key,
                        'value'     => $chmosProject[$key] ?: 0
                    ];
                }
            }
        }

        if(array_key_exists('thema', $chmosProject) && count($chmosProject['thema'])) {
            foreach([$chmosProject['thema']] as $tag) {
                $normalizedTopic = $this->normalizeTopic($tag);
                if($normalizedTopic['name']) {
                    $normalizedProject['topics'][] = $normalizedTopic;
                }
            }
        }

        if(array_key_exists('tag', $chmosProject) && count($chmosProject['tag'])) {
            foreach($chmosProject['tag'] as $tag) {
                $normalizedTopic = $this->normalizeTopic($tag);
                if($normalizedTopic['name']) {
                    $normalizedProject['topics'][] = $normalizedTopic;
                }
            }
        }

        foreach($chmosProject['institutionen'] as $chmosContact) {
            $contact = [
                'foreignId' => $chmosContact['id'],
                'email' => $chmosContact['kontEmail'],
                'role' => $chmosContact['kontFunktion'],
                'phoneMobile' => $chmosContact['kontMobil'],
                'phone' => $chmosContact['kontTel'],
                'public' => !!$chmosContact['oeffentlich'],
                'name' => $chmosContact['name'],
                'lastName' => $chmosContact['kontNachname'],
                'firstName' => $chmosContact['kontVorname'],
                'country' => $chmosContact['land'],
                'zipCode' => $chmosContact['plz'],
                'city' => $chmosContact['ort'],
                'street' => $chmosContact['strasse'],
            ];

            if(!$contact['name'] && !$contact['firstName'] && !$contact['lastName']) {
                continue;
            }

            $normalizedProject['contacts'][] = $contact;
        }

        if(isset($chmosProject['webseite']) && $chmosProject['webseite']) {
            $normalizedProject['links'][] = [
                'label' => 'Website',
                'url'   => $chmosProject['webseite'],
            ];
        }

        foreach($chmosProject['institutionen'] as $chmosContact) {
            $lat = $chmosContact['n'] ? strval($chmosContact['n']) : null;
            $lng = $chmosContact['e'] ? strval($chmosContact['e']) : null;

            if(!$lat) {
                continue;
            }

            $normalizedProject['lat'] = $lat;
            $normalizedProject['lng'] = $lng;

            break; // break loop if one coordinate already found
        }

        return $normalizedProject;
    }

    public function normalizeTopic($chmosTopic)
    {
        $topic = null;

        $qb = $this->em->createQueryBuilder();
        $topics = $qb->select('t')
            ->from(Topic::class, 't')
            ->andWhere('t.name = :name')
            ->setParameter('name', $chmosTopic['textDe'])
            ->andWhere('t.context = :context OR t.context IS NULL')
            ->setParameter('context', 'project')
            ->getQuery()
            ->getResult()
        ;

        if(count($topics)) {
            $topic = $topics[0];
        }

        if(!$topic) {
            $qb = $this->em->createQueryBuilder();
            $topics = $qb
                ->select('t')
                ->from(Topic::class, 't')
                ->where('JSON_CONTAINS(t.synonyms, :synonym) = 1')
                ->setParameter('synonym', '"'.trim($chmosTopic['textDe']).'"')
                ->andWhere('t.context = :context OR t.context IS NULL')
                ->setParameter('context', 'project')
                ->getQuery()
                ->getResult()
            ;
            if(count($topics)) {
                $topic = $topics[0];
            } else {
                $topics = $this->em->getRepository(Topic::class)->findBy([
                    'context' => 'project',
                ]);
                foreach($topics as $t) {
                    foreach($t->getSynonyms() as $synonym) {
                        if($synonym === $chmosTopic['textDe']) {
                            $topic = $t;
                            break;
                        }
                    }
                }
            }
        }

        if($topic) {
            return [
                'id'   => $topic->getId(),
                'name' => $topic->getName(),
                'foreignId' => $chmosTopic['id']
            ];
        }

        return [
            'id'   => null,
            'name' => $chmosTopic['textDe'],
            'foreignId' => $chmosTopic['id']
        ];
    }

    public function normalizeProgram($chmosProgram)
    {
        $program = $this->em->getRepository(Program::class)->findOneBy([
            'name' => $chmosProgram,
        ]);

        if(!$program) {
            $qb = $this->em->createQueryBuilder();
            $programs = $qb
                ->select('t')
                ->from(Program::class, 't')
                ->where('JSON_CONTAINS(t.synonyms, :synonym) = 1')
                ->setParameter('synonym', '"'.$chmosProgram.'"')
                ->getQuery()
                ->getResult()
            ;
            if(count($programs)) {
                $program = $programs[0];
            } else {
                $programs = $this->em->getRepository(Program::class)->findAll();
                foreach($programs as $p) {
                    foreach($p->getSynonyms() as $synonym) {
                        if($synonym === $chmosProgram) {
                            $program = $p;
                            break;
                        }
                    }
                }
            }
        }

        if($program) {
            return [
                'id'   => $program->getId(),
                'name' => $program->getName(),
                'foreignId' => $chmosProgram
            ];
        }

        return [
            'id'   => null,
            'name' => $chmosProgram,
            'foreignId' => $chmosProgram
        ];
    }

    public function normalizeInstrument($chmosInstrument)
    {
        $instrument = $this->em->getRepository(Instrument::class)->findOneBy([
            'name' => $chmosInstrument['textDe'],
        ]);

        if(!$instrument) {
            $qb = $this->em->createQueryBuilder();
            $instruments = $qb
                ->select('t')
                ->from(Instrument::class, 't')
                ->where('JSON_CONTAINS(t.synonyms, :synonym) = 1')
                ->setParameter('synonym', '"'.$chmosInstrument['textDe'].'"')
                ->getQuery()
                ->getResult()
            ;
            if(count($instruments)) {
                $instrument = $instruments[0];
            } else {
                $instruments = $this->em->getRepository(Instrument::class)->findAll();
                foreach($instruments as $i) {
                    foreach($i->getSynonyms() as $synonym) {
                        if($synonym === $chmosInstrument['textDe']) {
                            $instrument = $i;
                            break;
                        }
                    }
                }
            }
        }

        if($instrument) {
            return [
                'id'   => $instrument->getId(),
                'name' => $instrument->getName(),
                'foreignId' => $chmosInstrument['id']
            ];
        }

        return [
            'id'   => null,
            'name' => $chmosInstrument['textDe'],
            'foreignId' => $chmosInstrument['id']
        ];
    }

    public function normalizeCountry($chmosCountry)
    {
        $country = $this->em->getRepository(Country::class)->findOneBy([
            'name' => $chmosCountry,
        ]);

        if(!$country) {
            $qb = $this->em->createQueryBuilder();
            $countries = $qb
                ->select('t')
                ->from(Country::class, 't')
                ->where('JSON_CONTAINS(t.synonyms, :synonym) = 1')
                ->setParameter('synonym', '"'.$chmosCountry.'"')
                ->getQuery()
                ->getResult()
            ;
            if(count($countries)) {
                $country = $countries[0];
            } else {
                $countries = $this->em->getRepository(Country::class)->findAll();
                foreach($countries as $c) {
                    foreach($c->getSynonyms() as $synonym) {
                        if($synonym === $chmosCountry) {
                            $country = $c;
                            break;
                        }
                    }
                }
            }
        }

        if($country) {
            return [
                'id'   => $country->getId(),
                'name' => $country->getName(),
                'foreignId' => $chmosCountry
            ];
        }

        return [
            'id'   => null,
            'name' => $chmosCountry,
            'foreignId' => $chmosCountry
        ];
    }

    public function normalizeStates($chmosStates)
    {
        $result = [];

        $chmosStatesTmp = [];

        foreach($chmosStates as $chmosState) {

            if($chmosState === 'Basel-Stadt/Basel-Landschaft') {
                $chmosStatesTmp[] = 'Basel-Stadt';
                $chmosStatesTmp[] = 'Basel-Landschaft';
            } else {
                $chmosStatesTmp[] = $chmosState;
            }

        }

        foreach($chmosStatesTmp as $chmosState) {

            if(!$chmosState) {
                continue;
            }

            $state = $this->em->getRepository(State::class)->findOneBy([
                'name' => $chmosState,
            ]);

            if(!$state) {
                $qb = $this->em->createQueryBuilder();
                $states = $qb
                    ->select('t')
                    ->from(State::class, 't')
                    ->where('JSON_CONTAINS(t.synonyms, :synonym) = 1')
                    ->setParameter('synonym', '"'.$chmosState.'"')
                    ->getQuery()
                    ->getResult()
                ;
                if(count($states)) {
                    $state = $states[0];
                } else {
                    $states = $this->em->getRepository(State::class)->findAll();
                    foreach($states as $s) {
                        foreach($s->getSynonyms() as $synonym) {
                            if($synonym === $chmosState) {
                                $state = $s;
                                break;
                            }
                        }
                    }
                }
            }

            if($state) {
                $result[] = [
                    'id'   => $state->getId(),
                    'name' => $state->getName(),
                    'foreignId' => $chmosState
                ];
                continue;
            }

            $result[] = [
                'id'   => null,
                'name' => $chmosState,
                'foreignId' => $chmosState
            ];
        }

        return $result;
    }

    public function applyTags($normalizedProject)
    {
        foreach(['topics', 'instruments', 'programs', 'countries', 'states'] as $property) {

            foreach($normalizedProject[$property] as $item) {

                if ($item['id']) {
                    continue;
                }

                $tag = $this->em->getRepository(Tag::class)->findOneBy([
                    'name' => $item['name'],
                ]);

                if(!$tag) {
                    $tag = new Tag();

                    $tag
                        ->setCreatedAt(new \DateTime())
                        ->setIsPublic(true)
                    ;
                }

                $tag
                    ->setName($item['name'])
                ;

                $this->em->persist($tag);
                $this->em->flush();

                $normalizedProject['tags'][] = [
                    'id' => $tag->getId(),
                    'name' => $tag->getName(),
                ];

            }
        }

        return $normalizedProject;
    }

    public function identifyProject ($projectCode)
    {
        $project = $this->em
            ->getRepository(Project::class)
            ->findOneBy([
                'projectCode' => $projectCode
            ]);

        if(!$project) {
            $codeParts = explode('_', $projectCode);
            $projects = $this->em
                ->createQueryBuilder()
                ->select('p')
                ->from(Project::class, 'p')
                ->where('p.projectCode LIKE :part1')
                ->andWhere('p.projectCode LIKE :part2')
                ->setParameter(':part1', $codeParts[0].'%')
                ->setParameter(':part2', '%'.$codeParts[1])
                ->getQuery()
                ->getResult()
            ;

            if(count($projects)) {
                $project = $projects[0];
            }
        }

        return $project;
    }

}