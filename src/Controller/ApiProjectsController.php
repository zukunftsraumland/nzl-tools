<?php

namespace App\Controller;

use App\Service\ProjectService;
use App\Util\PvTrans;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\EntityManagerInterface;
use Nelmio\ApiDocBundle\Annotation\Model;
use Nelmio\ApiDocBundle\Annotation\Security;
use OpenApi\Attributes as OA;
use App\Entity\Inbox;
use App\Entity\Project;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;

#[Route(path: '/api/v1/projects', name: 'api_projects_')]
class ApiProjectsController extends AbstractController
{
    
    #[Route(path: '', name: 'index', methods: ['GET'])]
    #[OA\Parameter(
        name: 'ids[]',
        description: 'Set specific ids to select',
        in: 'query',
        required: false,
        schema: new OA\Schema(type: 'array', items: new OA\Items(type: 'integer')),
    )]
    #[OA\Parameter(
        name: 'term',
        description: 'Search term',
        in: 'query',
        required: false,
        schema: new OA\Schema(type: 'string'),
    )]
    #[OA\Parameter(
        name: 'startDate[]',
        description: 'Include only projects with given start dates (+1 year)',
        in: 'query',
        required: false,
        schema: new OA\Schema(type: 'array', items: new OA\Items(type: 'string', format: 'date')),
        style: 'deepObject',
    )]
    #[OA\Parameter(
        name: 'endDate[]',
        description: 'Include only projects with given end dates (+1 year)',
        in: 'query',
        required: false,
        schema: new OA\Schema(type: 'array', items: new OA\Items(type: 'string', format: 'date')),
    )]
    #[OA\Parameter(
        name: 'cooperation[]',
        description: 'Include only specific cooperations',
        in: 'query',
        required: false,
        schema: new OA\Schema(type: 'array', items: new OA\Items(type: 'string', enum: ['international', 'national', 'inter-cantonal', 'cantonal'])),
    )]
    #[OA\Parameter(
        name: 'state[]',
        description: 'Include only specific states (both name or id are valid values)',
        in: 'query',
        required: false,
        schema: new OA\Schema(type: 'array', items: new OA\Items(type: 'string')),
    )]
    #[OA\Parameter(
        name: 'topic[]',
        description: 'Include only specific topics (both name or id are valid values)',
        in: 'query',
        required: false,
        schema: new OA\Schema(type: 'array', items: new OA\Items(type: 'string')),
    )]
    #[OA\Parameter(
        name: 'tag[]',
        description: 'Include only specific tags (both name or id are valid values)',
        in: 'query',
        required: false,
        schema: new OA\Schema(type: 'array', items: new OA\Items(type: 'string')),
    )]
    #[OA\Parameter(
        name: 'program[]',
        description: 'Include only specific programs (both name or id are valid values)',
        in: 'query',
        required: false,
        schema: new OA\Schema(type: 'array', items: new OA\Items(type: 'string')),
    )]
    #[OA\Parameter(
        name: 'instrument[]',
        description: 'Include only specific instruments (both name or id are valid values)',
        in: 'query',
        required: false,
        schema: new OA\Schema(type: 'array', items: new OA\Items(type: 'string')),
    )]
    #[OA\Parameter(
        name: 'geographicRegion[]',
        description: 'Include only specific geographic regions (both name or id are valid values)',
        in: 'query',
        required: false,
        schema: new OA\Schema(type: 'array', items: new OA\Items(type: 'string')),
    )]
    #[OA\Parameter(
        name: 'businessSector[]',
        description: 'Include only specific business sectors (both name or id are valid values)',
        in: 'query',
        required: false,
        schema: new OA\Schema(type: 'array', items: new OA\Items(type: 'string')),
    )]
    #[OA\Parameter(
        name: 'status[]',
        description: 'Return projects by status',
        in: 'query',
        required: false,
        schema: new OA\Schema(type: 'string', enum: ['public', 'draft']),
    )]
    #[OA\Parameter(
        name: 'randomize',
        description: 'Randomize projects by a frequently updated random seed',
        in: 'query',
        required: false,
        schema: new OA\Schema(type: 'integer', enum: [0, 1]),
    )]
    #[OA\Parameter(
        name: 'limit',
        description: 'Limit returned items',
        in: 'query',
        required: false,
        schema: new OA\Schema(type: 'integer'),
    )]
    #[OA\Parameter(
        name: 'offset',
        description: 'Skip returned items',
        in: 'query',
        required: false,
        schema: new OA\Schema(type: 'integer'),
    )]
    #[OA\Parameter(
        name: 'orderBy[]',
        description: 'Order items by field',
        in: 'query',
        required: false,
        schema: new OA\Schema(type: 'string', enum: ['id', 'createdAt', 'updatedAt']),
    )]
    #[OA\Parameter(
        name: 'orderDirection[]',
        description: 'Set order direction',
        in: 'query',
        required: false,
        schema: new OA\Schema(type: 'string', enum: ['ASC', 'DESC']),
    )]
    #[OA\Response(
        response: 200,
        description: 'Returns all projects',
        content: new OA\JsonContent(
            type: 'array',
            items: new OA\Items(ref: new Model(type: Project::class, groups: ['id', 'project']))
        )
    )]
    #[OA\Tag(name: 'Projects')]
    public function index(Request $request, EntityManagerInterface $em, NormalizerInterface $normalizer): JsonResponse
    {
        $qb = $em->createQueryBuilder();
        
        $qb
            ->select('p')
            ->from(Project::class, 'p')
        ;
        
        if(!$this->isGranted('ROLE_EDITOR')) {
            $qb->andWhere('p.isPublic = TRUE');
        }

        if($request->get('ids') && !is_array($request->get('ids'))) {
            $qb
                ->andWhere('p.id IN (:ids)')
                ->setParameter('ids', array_map('trim', explode(',', $request->get('ids'))))
            ;
        }

        if($request->get('ids') && is_array($request->get('ids'))) {
            $qb
                ->andWhere('p.id IN (:ids)')
                ->setParameter('ids', $request->get('ids'))
            ;
        }
        
        if($request->get('term')) {
            $qb
                ->andWhere('(p.searchIndex LIKE :term OR p.title LIKE :term OR p.description LIKE :term OR p.translations LIKE :term)')
                ->setParameter('term', '%'.$request->get('term').'%');
        }
        
        if($request->get('startDate') && is_array($request->get('startDate')) && count($request->get('startDate'))) {

            $startDateQuery = [];

            foreach($request->get('startDate') as $key => $startDate) {
                $startDateFrom = new \DateTime($startDate);
                $startDateTo = (new \DateTime($startDate))->modify('+1 year');
                $startDateQuery[] = '(p.startDate >= :startDateFrom'.$key.' AND p.startDate < :startDateTo'.$key.')';
                $qb
                    ->setParameter('startDateFrom'.$key, $startDateFrom, Types::DATETIME_MUTABLE)
                    ->setParameter('startDateTo'.$key, $startDateTo, Types::DATETIME_MUTABLE)
                ;
            }

            $qb->andWhere(implode(' OR ', $startDateQuery));

        }
        
        if($request->get('endDate') && is_array($request->get('endDate')) && count($request->get('endDate'))) {

            $endDateQuery = [];

            foreach($request->get('endDate') as $key => $endDate) {
                $endDateFrom = new \DateTime($endDate);
                $endDateTo = (new \DateTime($endDate))->modify('+1 year');
                $endDateQuery[] = '(p.endDate >= :endDateFrom'.$key.' AND p.endDate < :endDateTo'.$key.')';
                $qb
                    ->setParameter('endDateFrom'.$key, $endDateFrom, Types::DATETIME_MUTABLE)
                    ->setParameter('endDateTo'.$key, $endDateTo, Types::DATETIME_MUTABLE)
                ;
            }

            $qb->andWhere(implode(' OR ', $endDateQuery));

        }
        
        if($request->get('cooperation') && is_array($request->get('cooperation')) && count($request->get('cooperation'))) {

            $cooperationQuery = [];

            $qb
                ->leftJoin('p.programs', 'programCooperation')
            ;

            foreach($request->get('cooperation') as $key => $cooperation) {

                if($cooperation === 'international') {
                    $cooperationQuery[] = 'programCooperation.name = :programInternationalCooperationA'.$key.' OR programCooperation.name = :programInternationalCooperationB'.$key;
                    $qb
                        ->setParameter('programInternationalCooperationA'.$key, 'ESPON')
                        ->setParameter('programInternationalCooperationB'.$key, 'Interreg')
                    ;
                }

                if($cooperation === 'national') {
                    $cooperationQuery[] = 'programCooperation.name = :programNationalCooperationA'.$key.' OR SIZE(p.states) = 0 OR SIZE(p.states) >= 26';
                    $qb
                        ->setParameter('programNationalCooperationA'.$key, 'Innotour')
                    ;
                }

                if($cooperation === 'inter-cantonal') {
                    $cooperationQuery[] = 'SIZE(p.states) > 1 AND SIZE(p.states) < 26';
                }

                if($cooperation === 'cantonal') {
                    $cooperationQuery[] = 'SIZE(p.states) = 1';
                }

            }

            $qb->andWhere(implode(' OR ', $cooperationQuery));

        }
        
        if($request->get('state') && is_array($request->get('state')) && count($request->get('state'))) {
            foreach($request->get('state') as $key => $state) {
                $qb
                    ->leftJoin('p.states', 'state'.$key)
                    ->andWhere('state'.$key.'.name = :state'.$key.' OR state'.$key.'.id = :stateId'.$key)
                    ->setParameter('state'.$key, $state)
                    ->setParameter('stateId'.$key, $state)
                ;
            }
        }
        
        if($request->get('topic') && is_array($request->get('topic')) && count($request->get('topic'))) {
            foreach($request->get('topic') as $key => $topic) {
                $qb
                    ->leftJoin('p.topics', 'topic'.$key)
                    ->andWhere('topic'.$key.'.name = :topic'.$key.' OR topic'.$key.'.id = :topicId'.$key)
                    ->setParameter('topic'.$key, $topic)
                    ->setParameter('topicId'.$key, $topic)
                ;
            }
        }

        if($request->get('tag') && is_array($request->get('tag')) && count($request->get('tag'))) {
            foreach($request->get('tag') as $key => $topic) {
                $qb
                    ->leftJoin('p.tags', 'tag'.$key)
                    ->andWhere('tag'.$key.'.name = :tag'.$key.' OR tag'.$key.'.id = :tagId'.$key)
                    ->setParameter('tag'.$key, $topic)
                    ->setParameter('tagId'.$key, $topic)
                ;
            }
        }
        
        if($request->get('program') && is_array($request->get('program')) && count($request->get('program'))) {

            $programQuery = [];

            foreach($request->get('program') as $key => $program) {

                $programQuery[] = 'program'.$key.'.name = :program'.$key.' OR program'.$key.'.id = :programId'.$key;

                $qb
                    ->leftJoin('p.programs', 'program'.$key)
                    ->setParameter('program'.$key, $program)
                    ->setParameter('programId'.$key, $program)
                ;
            }

            $qb
                ->andWhere(implode(' OR ', $programQuery))
            ;
        }
        
        if($request->get('instrument') && is_array($request->get('instrument')) && count($request->get('instrument'))) {

            $instrumentQuery = [];

            foreach($request->get('instrument') as $key => $instrument) {

                $instrumentQuery[] = 'instrument'.$key.'.name = :instrument'.$key.' OR instrument'.$key.'.id = :instrumentId'.$key;

                $qb
                    ->leftJoin('p.instruments', 'instrument'.$key)
                    ->setParameter('instrument'.$key, $instrument)
                    ->setParameter('instrumentId'.$key, $instrument)
                ;
            }

            $qb
                ->andWhere(implode(' OR ', $instrumentQuery))
            ;
        }

        if($request->get('geographicRegion') && is_array($request->get('geographicRegion')) && count($request->get('geographicRegion'))) {
            foreach($request->get('geographicRegion') as $key => $geographicRegion) {
                $qb
                    ->leftJoin('p.geographicRegions', 'geographicRegion'.$key)
                    ->andWhere('geographicRegion'.$key.'.name = :geographicRegion'.$key.' OR geographicRegion'.$key.'.id = :geographicRegionId'.$key)
                    ->setParameter('geographicRegion'.$key, $geographicRegion)
                    ->setParameter('geographicRegionId'.$key, $geographicRegion)
                ;
            }
        }

        if($request->get('businessSector') && is_array($request->get('businessSector')) && count($request->get('businessSector'))) {
            foreach($request->get('businessSector') as $key => $businessSector) {
                $qb
                    ->leftJoin('p.businessSectors', 'businessSector'.$key)
                    ->andWhere('businessSector'.$key.'.name = :businessSector'.$key.' OR businessSector'.$key.'.id = :businessSectorId'.$key)
                    ->setParameter('businessSector'.$key, $businessSector)
                    ->setParameter('businessSectorId'.$key, $businessSector)
                ;
            }
        }

        if($request->get('status') && is_array($request->get('status')) && count($request->get('status'))) {

            $statusQuery = [];

            foreach($request->get('status') as $key => $status) {

                $statusQuery[] = 'p.isPublic = :isPublic'.$key;

                $qb
                    ->setParameter('isPublic'.$key, $status === 'public')
                ;
            }

            $qb
                ->andWhere(implode(' OR ', $statusQuery))
            ;

        }

        if($request->get('orderBy') && is_array($request->get('orderBy')) && count($request->get('orderBy'))) {

            foreach($request->get('orderBy') as $key => $orderBy) {

                if(!in_array($orderBy, ['id', 'createdAt', 'updatedAt'])) {
                    continue;
                }

                $direction = 'ASC';

                if($request->get('orderDirection') && is_array($request->get('orderDirection')) &&
                    count($request->get('orderDirection')) && array_key_exists($key, $request->get('orderDirection')) &&
                    in_array($request->get('orderDirection')[$key], ['ASC', 'DESC'])) {
                        $direction = $request->get('orderDirection')[$key];
                }

                $qb
                    ->addOrderBy('p.'.$orderBy, $direction)
                ;

            }

        } else if ($request->get('randomize')) {
            $qb
                ->addOrderBy('p.random', 'DESC')
            ;
        } else {
            $qb
                ->addOrderBy('p.id', 'DESC')
            ;
        }
        
        if($request->get('limit')) {
            $qb->setMaxResults($request->get('limit'));
        }
        
        if($request->get('offset')) {
            $qb->setFirstResult($request->get('offset'));
        }
        
        $projects = $qb->getQuery()->getResult();

        $result = $normalizer->normalize($projects, null, [
            'groups' => ['id', 'project', 'topic', 'program', 'instrument', 'state', 'country', 'geographic_region', 'business_sector'],
        ]);

        return $this->json($result);
    }
    
    #[Route(path: '/{id}', name: 'get', methods: ['GET'])]
    #[OA\Response(
        response: 200,
        description: 'Returns a single project',
        content: new OA\JsonContent(
            ref: new Model(type: Project::class, groups: ['id', 'project'])
        )
    )]
    #[OA\Tag(name: 'Projects')]
    public function find(Request $request, EntityManagerInterface $em, NormalizerInterface $normalizer): JsonResponse
    {
        $project = $em->getRepository(Project::class)
            ->find($request->get('id'));

        $result = $normalizer->normalize($project, null, [
            'groups' => ['id', 'project', 'topic', 'program', 'instrument', 'state', 'country', 'geographic_region', 'business_sector'],
        ]);

        return $this->json($result);
    }
    
    #[Route(path: '', name: 'create', methods: ['POST'])]
    #[IsGranted('ROLE_EDITOR')]
    #[OA\Response(
        response: 200,
        description: 'Create a project',
        content: new OA\JsonContent(
            ref: new Model(type: Project::class, groups: ['id', 'project'])
        )
    )]
    #[OA\Tag(name: 'Projects')]
    #[Security(name: 'cookieAuth')]
    public function create(Request $request, EntityManagerInterface $em, NormalizerInterface $normalizer, ProjectService $projectService): JsonResponse
    {
        $payload = json_decode($request->getContent(), true);

        if(($errors = $projectService->validateProjectPayload($payload)) !== true) {
            return $this->json($errors, 400);
        }

        if(array_key_exists('addToInbox', $payload) && $payload['addToInbox']) {

            $inbox = new Inbox();
            $inbox->setCreatedAt(new \DateTime());
            $inbox->setSource('regiosuisse');
            $inbox->setType('project');
            $inbox->setIsMerged(false);
            $inbox->setStatus('new');

            if(array_key_exists('inboxId', $payload) && $payload['inboxId']) {
                $inbox = $em->getRepository(Inbox::class)->find($payload['inboxId']);
                $inbox->setUpdatedAt(new \DateTime());
            }

            $inbox->setTitle($payload['title']);
            $inbox->setData($payload);

            unset($payload['addToInbox']);
            unset($payload['inboxId']);

            $payload['startDate'] = $payload['startDate'] ? (date('Y-m-d H:i:s', strtotime($payload['startDate']))) : null;
            $payload['endDate'] = $payload['endDate'] ? (date('Y-m-d H:i:s', strtotime($payload['endDate']))) : null;

            $inbox->setNormalizedData($payload);
            $inbox->setCreatedAt(new \DateTime());

            $em->persist($inbox);
            $em->flush();

            $payload['inboxId'] = $inbox->getId();

            $result = $normalizer->normalize($inbox, null, [
                'groups' => ['id', 'inbox'],
            ]);

            return $this->json($result);

        }

        $project = $projectService->createProject($payload);

        $result = $normalizer->normalize($project, null, [
            'groups' => ['id', 'project'],
        ]);

        return $this->json($result);
    }
    
    #[Route(path: '/{id}', name: 'update', methods: ['PUT'])]
    #[IsGranted('ROLE_EDITOR')]
    #[OA\Response(
        response: 200,
        description: 'Update a project',
        content: new OA\JsonContent(
            ref: new Model(type: Project::class, groups: ['id', 'project'])
        )
    )]
    #[OA\Tag(name: 'Projects')]
    #[Security(name: 'cookieAuth')]
    public function update(Request $request, EntityManagerInterface $em, NormalizerInterface $normalizer,
                           ProjectService $projectService): JsonResponse
    {
        $project = $em->getRepository(Project::class)
            ->find($request->get('id'));

        $payload = json_decode($request->getContent(), true);

        if(($errors = $projectService->validateProjectPayload($payload)) !== true) {
            return $this->json($errors, 400);
        }

        if(array_key_exists('addToInbox', $payload) && $payload['addToInbox']) {

            $inbox = new Inbox();
            $inbox->setCreatedAt(new \DateTime());
            $inbox->setSource('regiosuisse');
            $inbox->setType('project');
            $inbox->setIsMerged(false);
            $inbox->setStatus('update');

            if(array_key_exists('inboxId', $payload) && $payload['inboxId']) {
                $inbox = $em->getRepository(Inbox::class)->find($payload['inboxId']);
                $inbox->setUpdatedAt(new \DateTime());
            }

            $inbox->setInternalId($request->get('id'));
            $inbox->setTitle($payload['title']);
            $inbox->setData($payload);

            unset($payload['addToInbox']);
            unset($payload['inboxId']);

            $payload['startDate'] = $payload['startDate'] ? (date('Y-m-d H:i:s', strtotime($payload['startDate']))) : null;
            $payload['endDate'] = $payload['endDate'] ? (date('Y-m-d H:i:s', strtotime($payload['endDate']))) : null;

            $inbox->setNormalizedData($payload);

            $em->persist($inbox);
            $em->flush();

            $payload['inboxId'] = $inbox->getId();

            $result = $normalizer->normalize($inbox, null, [
                'groups' => ['id', 'inbox'],
            ]);

            return $this->json($result);

        }

        $project = $projectService->updateProject($project, $payload);

        $result = $normalizer->normalize($project, null, [
            'groups' => ['id', 'project'],
        ]);

        return $this->json($result);
    }
    
    #[Route(path: '/{id}', name: 'delete', methods: ['DELETE'])]
    #[IsGranted('ROLE_EDITOR')]
    #[OA\Response(
        response: 200,
        description: 'Delete a project',
        content: new OA\JsonContent(
            type: 'array',
            items: new OA\Items(type: 'string'),
            default: []
        )
    )]
    #[OA\Tag(name: 'Projects')]
    #[Security(name: 'cookieAuth')]
    public function delete(Request $request, EntityManagerInterface $em, NormalizerInterface $normalizer, ProjectService $projectService): JsonResponse
    {
        $project = $em->getRepository(Project::class)
            ->find($request->get('id'));

        $projectService->deleteProject($project);

        return $this->json([]);
    }

    #[Route(path: '.xlsx', name: 'xlsx', methods: ['GET'])]
    #[IsGranted('ROLE_EDITOR')]
    #[OA\Response(
        response: 200,
        description: 'Returns a file',
        content: new OA\MediaType(mediaType: 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', schema: new OA\Schema(type: 'string', format: 'binary'))
    )]
    #[OA\Tag(name: 'Projects')]
    #[Security(name: 'cookieAuth')]
    public function xlsx(Request $request, EntityManagerInterface $em, NormalizerInterface $normalizer, ProjectService $projectService): Response
    {
        $projects = $em->getRepository(Project::class)
            ->findBy([], [
                'id' => 'ASC',
            ]);

        $spreadsheet = new Spreadsheet();
        $spreadsheet->removeSheetByIndex(0);

        foreach(['de', 'fr', 'it'] as $localeIndex => $locale) {

            $sheet = new Worksheet(null, 'Projekte - '.strtoupper($locale));
            $spreadsheet->addSheet($sheet, $localeIndex);

            $sheet = $spreadsheet->getSheet($localeIndex);

            $columns = [
                'ID' => 'A',
                'Status' => 'B',
                'Projektcode' => 'C',
                'Projekttitel' => 'D',
                'Beschreibung' => 'E',
                'Beschreibung (Formatiert)' => 'F',
                'Programme' => 'G',
                'Finanzierung' => 'H',
                'Themen' => 'I',
                'Geographische Regionen' => 'J',
                'Länder' => 'K',
                'Kantone' => 'L',
                'Projektkosten' => 'M',
                'Weitere Projektkosten' => 'N',
                'Start Datum' => 'O',
                'End Datum' => 'P',
                'Links' => 'Q',
                'Bilder' => 'R',
                'Dokumente' => 'S',
                'Videos' => 'T',
                'Kontakte' => 'U',
            ];

            $row = 1;

            foreach($columns as $columnLabel => $column) {
                $sheet->getColumnDimension($column)->setWidth(20);
                $sheet->setCellValue($column.$row, $columnLabel);
            }

            $sheet->getColumnDimension($columns['Beschreibung'])->setWidth(100);
            $sheet->getColumnDimension($columns['Beschreibung (Formatiert)'])->setWidth(100);
            $sheet->getColumnDimension($columns['Themen'])->setWidth(60);
            $sheet->getColumnDimension($columns['Links'])->setWidth(60);
            $sheet->getColumnDimension($columns['Bilder'])->setWidth(60);
            $sheet->getColumnDimension($columns['Dokumente'])->setWidth(60);
            $sheet->getColumnDimension($columns['Videos'])->setWidth(60);
            $sheet->getColumnDimension($columns['Kontakte'])->setWidth(60);

            $row++;

            /** @var Project $project */
            foreach($projects as $project) {
                $sheet->setCellValue($columns['ID'].$row, $project->getId());
                $sheet->setCellValue($columns['Status'].$row, $project->getIsPublic() ? 'Öffentlich' : 'Entwurf');
                $sheet->setCellValue($columns['Projektcode'].$row, $project->getProjectCode());
                $sheet->setCellValue($columns['Projekttitel'].$row, PvTrans::trans($project, 'title', $locale));
                $sheet->setCellValue($columns['Beschreibung'].$row, html_entity_decode(strip_tags(PvTrans::trans($project, 'description', $locale) ?: '')));
                $sheet->setCellValue($columns['Beschreibung (Formatiert)'].$row, PvTrans::trans($project, 'description', $locale));

                $entities = [];
                foreach($project->getPrograms() as $entity) {
                    $entities[] = PvTrans::trans($entity, 'name', $locale);
                }
                $sheet->setCellValue($columns['Programme'].$row, implode(', ', $entities));

                $entities = [];
                foreach($project->getInstruments() as $entity) {
                    $entities[] = PvTrans::trans($entity, 'name', $locale);
                }
                $sheet->setCellValue($columns['Finanzierung'].$row, implode(', ', $entities));

                $entities = [];
                foreach($project->getTopics() as $entity) {
                    $entities[] = PvTrans::trans($entity, 'name', $locale);
                }
                $sheet->setCellValue($columns['Themen'].$row, implode(', ', $entities));

                $entities = [];
                foreach($project->getGeographicRegions() as $entity) {
                    $entities[] = PvTrans::trans($entity, 'name', $locale);
                }
                $sheet->setCellValue($columns['Geographische Regionen'].$row, implode(', ', $entities));

                $entities = [];
                foreach($project->getCountries() as $entity) {
                    $entities[] = PvTrans::trans($entity, 'name', $locale);
                }
                $sheet->setCellValue($columns['Länder'].$row, implode(', ', $entities));

                $entities = [];
                foreach($project->getStates() as $entity) {
                    $entities[] = PvTrans::trans($entity, 'name', $locale);
                }
                $sheet->setCellValue($columns['Kantone'].$row, implode(', ', $entities));

                $sheet->setCellValue($columns['Projektkosten'].$row, $project->getProjectCosts());

                $entities = [];

                foreach($project->getFinancing() as $financing) {
                    $financialMapping = [
                        'costsFederation' => 'Förderung Bund',
                        'costsCanton' => 'Förderung Kanton(e)',
                        'costsExternal' => 'Finanzierung Dritte',
                        'costsEU' => 'Gesamtkosten EU',
                    ];
                    if(!array_key_exists($financing['id'], $financialMapping)) {
                        continue;
                    }
                    $entities[] = $financialMapping[$financing['id']].': '.$financing['value'];
                }

                $sheet->setCellValue($columns['Weitere Projektkosten'].$row, implode(PHP_EOL, $entities));

                $sheet->setCellValue($columns['Start Datum'].$row, $project->getStartDate() ? $project->getStartDate()->format('d.m.Y') : '');
                $sheet->setCellValue($columns['End Datum'].$row, $project->getEndDate() ? $project->getEndDate()->format('d.m.Y') : '');

                $entities = [];
                foreach((PvTrans::trans($project, 'links', $locale) ?: []) as $entity) {
                    if(array_key_exists('url', $entity)) {
                        $entities[] = $entity['label'].': '.$entity['url'];
                    }
                }
                $sheet->setCellValue($columns['Links'].$row, implode(PHP_EOL, $entities));

                $entities = [];
                foreach((PvTrans::trans($project, 'files', $locale) ?: []) as $entity) {
                    $entities[] = $this->generateUrl('api_files_download', [
                        'id' => $entity['id'],
                        'extension' => $entity['extension'],
                    ], UrlGeneratorInterface::ABSOLUTE_URL);
                }
                $sheet->setCellValue($columns['Dokumente'].$row, implode(PHP_EOL, $entities));

                $entities = [];
                foreach($project->getImages() as $entity) {
                    $entities[] = $this->generateUrl('api_files_download', [
                        'id' => $entity['id'],
                        'extension' => $entity['extension'],
                    ], UrlGeneratorInterface::ABSOLUTE_URL);
                }
                $sheet->setCellValue($columns['Bilder'].$row, implode(PHP_EOL, $entities));

                $entities = [];
                foreach((PvTrans::trans($project, 'videos', $locale) ?: []) as $entity) {
                    if(array_key_exists('url', $entity)) {
                        $entities[] = $entity['label'].': '.$entity['url'];
                    }
                }
                $sheet->setCellValue($columns['Videos'].$row, implode(PHP_EOL, $entities));

                $entities = [];
                foreach((PvTrans::trans($project, 'contacts', $locale) ?: []) as $entity) {
                    $contact = '';

                    if(isset($entity['name']) && $entity['name']) {
                        $contact.= $entity['name'].PHP_EOL;
                    }

                    if(isset($entity['firstName']) && isset($entity['lastName']) && $entity['firstName'] && $entity['lastName']) {

                        if(isset($entity['title']) && $entity['title']) {
                            $contact.= $entity['title'].' ';
                        }

                        $contact.= $entity['firstName'].' '.$entity['lastName'].PHP_EOL;
                    }

                    if(isset($entity['role']) && $entity['role']) {
                        $contact.= $entity['role'].PHP_EOL;
                    }

                    if(isset($entity['street']) && $entity['street']) {
                        $contact.= $entity['street'].PHP_EOL;
                    }

                    if(isset($entity['zipCode']) && isset($entity['city']) && $entity['zipCode'] && $entity['city']) {
                        $contact.= $entity['zipCode'].' '.$entity['city'].PHP_EOL;
                    }

                    if(isset($entity['phone']) && $entity['phone']) {
                        $contact.= $entity['phone'].PHP_EOL;
                    }

                    if(isset($entity['email']) && $entity['email']) {
                        $contact.= $entity['email'].PHP_EOL;
                    }

                    if(isset($entity['website']) && $entity['website']) {
                        $contact.= $entity['website'].PHP_EOL;
                    }

                    if(!trim($contact)) {
                        continue;
                    }

                    $entities[] = $contact;
                }
                $sheet->setCellValue($columns['Kontakte'].$row, implode(PHP_EOL.PHP_EOL, $entities));

                $row++;
            }

        }

        $writer = new Xlsx($spreadsheet);

        $extension = 'xlsx';
        $fileName = 'Projekte-'.date('Y-m-d_H-i-s').'.'.$extension;

        $tmpFile = tempnam(sys_get_temp_dir(), $fileName);

        $writer->save($tmpFile);

        $response = $this->file($tmpFile, $fileName, ResponseHeaderBag::DISPOSITION_ATTACHMENT);

        $response->deleteFileAfterSend(true);

        return $response;
    }

    #[Route(path: '/geojson/{_locale}.json', name: 'geojson', methods: ['GET'])]
    #[OA\Parameter(
        name: 'term',
        description: 'Search term',
        in: 'query',
        required: false,
        schema: new OA\Schema(type: 'string'),
    )]
    #[OA\Parameter(
        name: 'startDate[]',
        description: 'Include only projects with given start dates (+1 year)',
        in: 'query',
        required: false,
        schema: new OA\Schema(type: 'array', items: new OA\Items(type: 'string', format: 'date')),
        style: 'deepObject',
    )]
    #[OA\Parameter(
        name: 'endDate[]',
        description: 'Include only projects with given end dates (+1 year)',
        in: 'query',
        required: false,
        schema: new OA\Schema(type: 'array', items: new OA\Items(type: 'string', format: 'date')),
    )]
    #[OA\Parameter(
        name: 'cooperation[]',
        description: 'Include only specific cooperations',
        in: 'query',
        required: false,
        schema: new OA\Schema(type: 'array', items: new OA\Items(type: 'string', enum: ['international', 'national', 'inter-cantonal', 'cantonal'])),
    )]
    #[OA\Parameter(
        name: 'state[]',
        description: 'Include only specific states (both name or id are valid values)',
        in: 'query',
        required: false,
        schema: new OA\Schema(type: 'array', items: new OA\Items(type: 'string')),
    )]
    #[OA\Parameter(
        name: 'topic[]',
        description: 'Include only specific topics (both name or id are valid values)',
        in: 'query',
        required: false,
        schema: new OA\Schema(type: 'array', items: new OA\Items(type: 'string')),
    )]
    #[OA\Parameter(
        name: 'tag[]',
        description: 'Include only specific tags (both name or id are valid values)',
        in: 'query',
        required: false,
        schema: new OA\Schema(type: 'array', items: new OA\Items(type: 'string')),
    )]
    #[OA\Parameter(
        name: 'program[]',
        description: 'Include only specific programs (both name or id are valid values)',
        in: 'query',
        required: false,
        schema: new OA\Schema(type: 'array', items: new OA\Items(type: 'string')),
    )]
    #[OA\Parameter(
        name: 'instrument[]',
        description: 'Include only specific instruments (both name or id are valid values)',
        in: 'query',
        required: false,
        schema: new OA\Schema(type: 'array', items: new OA\Items(type: 'string')),
    )]
    #[OA\Parameter(
        name: 'geographicRegion[]',
        description: 'Include only specific geographic regions (both name or id are valid values)',
        in: 'query',
        required: false,
        schema: new OA\Schema(type: 'array', items: new OA\Items(type: 'string')),
    )]
    #[OA\Parameter(
        name: 'businessSector[]',
        description: 'Include only specific business sectors (both name or id are valid values)',
        in: 'query',
        required: false,
        schema: new OA\Schema(type: 'array', items: new OA\Items(type: 'string')),
    )]
    #[OA\Response(
        response: 200,
        description: 'Returns geojson',
        content: new OA\JsonContent(
            type: 'array',
            items: new OA\Items(type: 'string'),
            default: []
        )
    )]
    #[OA\Tag(name: 'Projects')]
    public function geojson(Request $request, EntityManagerInterface $em,
                            NormalizerInterface $normalizer, CacheInterface $cache): JsonResponse
    {
        $query = $request->query->all();
        unset($query['limit']);
        unset($query['offset']);

        $key = json_encode($query);

        $geojson = $cache->get('projects.geojson.'.md5($key), function (ItemInterface $item) use ($em, $request, $normalizer, $query) {

            $item->expiresAfter(3600);

            $qb = $em->createQueryBuilder();

            $qb
                ->select('p')
                ->from(Project::class, 'p')
                ->andWhere('p.isPublic = :isPublic')
                ->setParameter('isPublic', true)
            ;

            if($request->get('term')) {
                $qb
                    ->andWhere('(p.searchIndex LIKE :term OR p.title LIKE :term OR p.description LIKE :term OR p.translations LIKE :term)')
                    ->setParameter('term', '%'.$request->get('term').'%');
            }

            if($request->get('startDate') && is_array($request->get('startDate')) && count($request->get('startDate'))) {

                $startDateQuery = [];

                foreach($request->get('startDate') as $key => $startDate) {
                    $startDateFrom = new \DateTime($startDate);
                    $startDateTo = (new \DateTime($startDate))->modify('+1 year');
                    $startDateQuery[] = '(p.startDate >= :startDateFrom'.$key.' AND p.startDate < :startDateTo'.$key.')';
                    $qb
                        ->setParameter('startDateFrom'.$key, $startDateFrom, Types::DATETIME_MUTABLE)
                        ->setParameter('startDateTo'.$key, $startDateTo, Types::DATETIME_MUTABLE)
                    ;
                }

                $qb->andWhere(implode(' OR ', $startDateQuery));

            }

            if($request->get('endDate') && is_array($request->get('endDate')) && count($request->get('endDate'))) {

                $endDateQuery = [];

                foreach($request->get('endDate') as $key => $endDate) {
                    $endDateFrom = new \DateTime($endDate);
                    $endDateTo = (new \DateTime($endDate))->modify('+1 year');
                    $endDateQuery[] = '(p.endDate >= :endDateFrom'.$key.' AND p.endDate < :endDateTo'.$key.')';
                    $qb
                        ->setParameter('endDateFrom'.$key, $endDateFrom, Types::DATETIME_MUTABLE)
                        ->setParameter('endDateTo'.$key, $endDateTo, Types::DATETIME_MUTABLE)
                    ;
                }

                $qb->andWhere(implode(' OR ', $endDateQuery));

            }

            if($request->get('cooperation') && is_array($request->get('cooperation')) && count($request->get('cooperation'))) {

                $cooperationQuery = [];

                $qb
                    ->leftJoin('p.programs', 'programCooperation')
                ;

                foreach($request->get('cooperation') as $key => $cooperation) {

                    if($cooperation === 'international') {
                        $cooperationQuery[] = 'programCooperation.name = :programInternationalCooperationA'.$key.' OR programCooperation.name = :programInternationalCooperationB'.$key;
                        $qb
                            ->setParameter('programInternationalCooperationA'.$key, 'ESPON')
                            ->setParameter('programInternationalCooperationB'.$key, 'Interreg')
                        ;
                    }

                    if($cooperation === 'national') {
                        $cooperationQuery[] = 'programCooperation.name = :programNationalCooperationA'.$key.' OR SIZE(p.states) = 0 OR SIZE(p.states) >= 26';
                        $qb
                            ->setParameter('programNationalCooperationA'.$key, 'Innotour')
                        ;
                    }

                    if($cooperation === 'inter-cantonal') {
                        $cooperationQuery[] = 'SIZE(p.states) > 1 AND SIZE(p.states) < 26';
                    }

                    if($cooperation === 'cantonal') {
                        $cooperationQuery[] = 'SIZE(p.states) = 1';
                    }

                }

                $qb->andWhere(implode(' OR ', $cooperationQuery));

            }

            if($request->get('state') && is_array($request->get('state')) && count($request->get('state'))) {
                foreach($request->get('state') as $key => $state) {
                    $qb
                        ->leftJoin('p.states', 'state'.$key)
                        ->andWhere('state'.$key.'.name = :state'.$key.' OR state'.$key.'.id = :stateId'.$key)
                        ->setParameter('state'.$key, $state)
                        ->setParameter('stateId'.$key, $state)
                    ;
                }
            }

            if($request->get('topic') && is_array($request->get('topic')) && count($request->get('topic'))) {
                foreach($request->get('topic') as $key => $topic) {
                    $qb
                        ->leftJoin('p.topics', 'topic'.$key)
                        ->andWhere('topic'.$key.'.name = :topic'.$key.' OR topic'.$key.'.id = :topicId'.$key)
                        ->setParameter('topic'.$key, $topic)
                        ->setParameter('topicId'.$key, $topic)
                    ;
                }
            }

            if($request->get('tag') && is_array($request->get('tag')) && count($request->get('tag'))) {
                foreach($request->get('tag') as $key => $topic) {
                    $qb
                        ->leftJoin('p.tags', 'tag'.$key)
                        ->andWhere('tag'.$key.'.name = :tag'.$key.' OR tag'.$key.'.id = :tagId'.$key)
                        ->setParameter('tag'.$key, $topic)
                        ->setParameter('tagId'.$key, $topic)
                    ;
                }
            }

            if($request->get('program') && is_array($request->get('program')) && count($request->get('program'))) {

                $programQuery = [];

                foreach($request->get('program') as $key => $program) {

                    $programQuery[] = 'program'.$key.'.name = :program'.$key.' OR program'.$key.'.id = :programId'.$key;

                    $qb
                        ->leftJoin('p.programs', 'program'.$key)
                        ->setParameter('program'.$key, $program)
                        ->setParameter('programId'.$key, $program)
                    ;
                }

                $qb
                    ->andWhere(implode(' OR ', $programQuery))
                ;
            }

            if($request->get('instrument') && is_array($request->get('instrument')) && count($request->get('instrument'))) {

                $instrumentQuery = [];

                foreach($request->get('instrument') as $key => $instrument) {

                    $instrumentQuery[] = 'instrument'.$key.'.name = :instrument'.$key.' OR instrument'.$key.'.id = :instrumentId'.$key;

                    $qb
                        ->leftJoin('p.instruments', 'instrument'.$key)
                        ->setParameter('instrument'.$key, $instrument)
                        ->setParameter('instrumentId'.$key, $instrument)
                    ;
                }

                $qb
                    ->andWhere(implode(' OR ', $instrumentQuery))
                ;
            }

            if($request->get('geographicRegion') && is_array($request->get('geographicRegion')) && count($request->get('geographicRegion'))) {
                foreach($request->get('geographicRegion') as $key => $geographicRegion) {
                    $qb
                        ->leftJoin('p.geographicRegions', 'geographicRegion'.$key)
                        ->andWhere('geographicRegion'.$key.'.name = :geographicRegion'.$key.' OR geographicRegion'.$key.'.id = :geographicRegionId'.$key)
                        ->setParameter('geographicRegion'.$key, $geographicRegion)
                        ->setParameter('geographicRegionId'.$key, $geographicRegion)
                    ;
                }
            }

            if($request->get('businessSector') && is_array($request->get('businessSector')) && count($request->get('businessSector'))) {
                foreach($request->get('businessSector') as $key => $businessSector) {
                    $qb
                        ->leftJoin('p.businessSectors', 'businessSector'.$key)
                        ->andWhere('businessSector'.$key.'.name = :businessSector'.$key.' OR businessSector'.$key.'.id = :businessSectorId'.$key)
                        ->setParameter('businessSector'.$key, $businessSector)
                        ->setParameter('businessSectorId'.$key, $businessSector)
                    ;
                }
            }

            $projects = $qb->getQuery()->getResult();

            $mainTopics = [
                [
                    'id' => 1,
                    'name' => 'Regionale Innovationssysteme (RIS)',
                ],
                [
                    'id' => 2,
                    'name' => 'Industrie',
                ],
                [
                    'id' => 3,
                    'name' => 'Tourismus',
                ],
                [
                    'id' => 4,
                    'name' => 'Sonstige',
                ],
            ];

            $geojson = [
                'type' => 'FeatureCollection',
                'features' => [],
            ];

            foreach($projects as $project) {
                if(!$project->getLng() || !$project->getLat()) {
                    continue;
                }

                $mainTopic = $mainTopics[count($mainTopics) - 1]['id'];

                foreach($project->getTopics()->toArray() as $topic) {

                    if($topic->getName() === 'Innovationsförderung (inkl. RIS)') {
                        $mainTopic = 1;
                        continue;
                    }
                    if($topic->getName() === 'Industrie & Gewerbe') {
                        $mainTopic = 2;
                        continue;
                    }
                    if($topic->getName() === 'Tourismus') {
                        $mainTopic = 3;
                        continue;
                    }
                }

                $geojson['features'][] = [
                    'type' => 'Feature',
                    'properties' => [
                        'id' => $project->getId(),
                        'title' => $project->getTitle(),
                        'mainTopic' => $mainTopic,
                        'topics' => array_map(function ($topic) {
                            return $topic->getId();
                        }, $project->getTopics()->toArray()),
                    ],
                    'geometry' => [
                        'type' => 'Point',
                        'coordinates' => [$project->getLng(), $project->getLat()],
                    ],
                ];
            }

            return $geojson;
        });

        return $this->json($geojson);
    }
    
}