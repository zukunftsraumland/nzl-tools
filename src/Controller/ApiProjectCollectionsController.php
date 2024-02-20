<?php

namespace App\Controller;

use App\Entity\Project;
use App\Entity\ProjectCollection;
use App\Service\ProjectCollectionService;
use Doctrine\ORM\EntityManagerInterface;
use Nelmio\ApiDocBundle\Annotation\Model;
use Nelmio\ApiDocBundle\Annotation\Security;
use OpenApi\Attributes as OA;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

#[Route(path: '/api/v1/project-collections', name: 'api_project_collections_')]
class ApiProjectCollectionsController extends AbstractController
{
    
    #[Route(path: '', name: 'index', methods: ['GET'])]
    #[OA\Response(
        response: 200,
        description: 'Returns all project collections',
        content: new OA\JsonContent(
            type: 'array',
            items: new OA\Items(ref: new Model(type: ProjectCollection::class, groups: ['id', 'project_collection']))
        )
    )]
    #[OA\Tag(name: 'Project Collections')]
    public function index(EntityManagerInterface $em, NormalizerInterface $normalizer): JsonResponse
    {
        $projectCollections = $em->getRepository(ProjectCollection::class)->findAll();

        $result = $normalizer->normalize($projectCollections, null, [
            'groups' => ['id', 'project_collection'],
        ]);

        return $this->json($result);
    }
    
    #[Route(path: '/{id}', name: 'get', methods: ['GET'])]
    #[OA\Response(
        response: 200,
        description: 'Returns a single project collection',
        content: new OA\JsonContent(
            ref: new Model(type: ProjectCollection::class, groups: ['id', 'project_collection'])
        )
    )]
    #[OA\Tag(name: 'Project Collections')]
    public function find(Request $request, EntityManagerInterface $em, NormalizerInterface $normalizer): JsonResponse
    {
        $projectCollection = $em->getRepository(ProjectCollection::class)
            ->find($request->get('id'));

        $result = $normalizer->normalize($projectCollection, null, [
            'groups' => ['id', 'project_collection'],
        ]);

        return $this->json($result);
    }
    
    #[Route(path: '', name: 'create', methods: ['POST'])]
    #[IsGranted('ROLE_EDITOR')]
    #[OA\Response(
        response: 200,
        description: 'Create a project collection',
        content: new OA\JsonContent(
            ref: new Model(type: ProjectCollection::class, groups: ['id', 'project_collection'])
        )
    )]
    #[OA\Tag(name: 'Project Collections')]
    #[Security(name: 'cookieAuth')]
    public function create(Request $request, EntityManagerInterface $em, NormalizerInterface $normalizer, 
                           ProjectCollectionService $projectCollectionService): JsonResponse
    {
        $payload = json_decode($request->getContent(), true);
        
        if(($errors = $projectCollectionService->validateProjectCollectionPayload($payload)) !== true) {
            return $this->json($errors, 400);
        }
        
        $projectCollection = $projectCollectionService->createProjectCollection($payload);

        $result = $normalizer->normalize($projectCollection, null, [
            'groups' => ['id', 'project_collection'],
        ]);

        return $this->json($result);
    }
    
    #[Route(path: '/{id}', name: 'update', methods: ['PUT'])]
    #[IsGranted('ROLE_EDITOR')]
    #[OA\Response(
        response: 200,
        description: 'Update a project collection',
        content: new OA\JsonContent(
            ref: new Model(type: ProjectCollection::class, groups: ['id', 'project_collection'])
        )
    )]
    #[OA\Tag(name: 'Project Collections')]
    #[Security(name: 'cookieAuth')]
    public function update(Request $request, EntityManagerInterface $em, NormalizerInterface $normalizer,
                           ProjectCollectionService $projectCollectionService): JsonResponse
    {
        $projectCollection = $em->getRepository(ProjectCollection::class)
            ->find($request->get('id'));
        
        $payload = json_decode($request->getContent(), true);
        
        if(($errors = $projectCollectionService->validateProjectCollectionPayload($payload)) !== true) {
            return $this->json($errors, 400);
        }
        
        $projectCollection = $projectCollectionService->updateProjectCollection($projectCollection, $payload);

        $result = $normalizer->normalize($projectCollection, null, [
            'groups' => ['id', 'project_collection'],
        ]);

        return $this->json($result);
    }
    
    #[Route(path: '/{id}', name: 'delete', methods: ['DELETE'])]
    #[IsGranted('ROLE_EDITOR')]
    #[OA\Response(
        response: 200,
        description: 'Delete a project collection',
        content: new OA\JsonContent(
            type: 'array',
            items: new OA\Items(type: 'string'),
            default: []
        )
    )]
    #[OA\Tag(name: 'Project Collections')]
    #[Security(name: 'cookieAuth')]
    public function delete(Request $request, EntityManagerInterface $em, NormalizerInterface $normalizer,
                           ProjectCollectionService $projectCollectionService): JsonResponse
    {
        $projectCollection = $em->getRepository(ProjectCollection::class)
            ->find($request->get('id'));
        
        $projectCollectionService->deleteProjectCollection($projectCollection);
        
        return $this->json([]);
    }
    
    #[Route(path: '/public/{id}', name: 'public', methods: ['GET'])]
    #[OA\Response(
        response: 200,
        description: 'Returns projects for a given project collection',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'title', type: 'string'),
                new OA\Property(
                    property: 'projects',
                    type: 'array',
                    items: new OA\Items(ref: new Model(type: Project::class, groups: ['id', 'project']))
                ),
            ],
            type: 'object'
        )
    )]
    #[OA\Tag(name: 'Project Collections')]
    public function publicAction(Request $request, EntityManagerInterface $em, NormalizerInterface $normalizer): JsonResponse
    {
        $projectCollection = $em
            ->getRepository(ProjectCollection::class)
            ->find($request->get('id'));
        
        if(!$projectCollection) {
            throw $this->createNotFoundException();
        }
        
        $result = [
            'title' => $projectCollection->getTitle(),
            'projects' => [],
        ];
        
        if(!$projectCollection->getIsDynamic()) {

            foreach($projectCollection->getSelection() as $selection) {

                $project = $em->getRepository(Project::class)
                    ->find($selection['id']);

                $result['projects'][] = $normalizer->normalize($project, null, [
                    'groups' => ['id', 'project', 'topic', 'program', 'instrument', 'state', 'country', 'geographic_region', 'business_sector'],
                ]);

            }

            return $this->json($result);

        }

        $qb = $em->getRepository(Project::class)
            ->createQueryBuilder('project')
            ->innerJoin('project.topics', 'topic')
            ->innerJoin('project.programs', 'program')
            ->innerJoin('project.states', 'state')
            ->innerJoin('project.instruments', 'instrument')
            ->innerJoin('project.countries', 'country')
            ->innerJoin('project.geographicRegions', 'geographicRegion')
            ->innerJoin('project.businessSectors', 'businessSector')
        ;

        foreach($projectCollection->getFilters() as $key => $filter) {

            if($filter['type'] === 'startDate') {
                $qb->andWhere('project.startDate = :startDate'.$key);
                $qb->setParameter('startDate'.$key, date('Y-m-d H:i:s', strtotime($filter['value'].'-01-01 00:00:00')));
            }

            if($filter['type'] === 'endDate') {
                $qb->andWhere('project.endDate = :endDate'.$key);
                $qb->setParameter('endDate'.$key, date('Y-m-d H:i:s', strtotime($filter['value'].'-12-31 23:59:59')));
            }

            if($filter['type'] === 'topic') {
                $qb->andWhere(':topic'.$key.' = topic.name');
                $qb->setParameter('topic'.$key, $filter['value']);
            }

            if($filter['type'] === 'state') {
                $qb->andWhere(':state'.$key.' = state.name');
                $qb->setParameter('state'.$key, $filter['value']);
            }

            if($filter['type'] === 'instrument') {
                $qb->andWhere(':instrument'.$key.' = instrument.name');
                $qb->setParameter('instrument'.$key, $filter['value']);
            }

            if($filter['type'] === 'program') {
                $qb->andWhere(':program'.$key.' = program.name');
                $qb->setParameter('program'.$key, $filter['value']);
            }

            if($filter['type'] === 'geographicRegion') {
                $qb->andWhere(':geographicRegion'.$key.' = geographicRegion.name');
                $qb->setParameter('geographicRegion'.$key, $filter['value']);
            }

            if($filter['type'] === 'businessSector') {
                $qb->andWhere(':businessSector'.$key.' = businessSector.name');
                $qb->setParameter('businessSector'.$key, $filter['value']);
            }

        }

        $projects = $qb->getQuery()->getResult();

        foreach($projects as $project) {

            $result['projects'][] = $normalizer->normalize($project, null, [
                'groups' => ['id', 'project', 'topic', 'program', 'instrument', 'state', 'country', 'geographic_region', 'business_sector'],
            ]);

        }

        return $this->json($result);
        
    }
    
}