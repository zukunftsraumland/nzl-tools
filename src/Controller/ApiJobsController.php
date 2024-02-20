<?php

namespace App\Controller;

use App\Service\JobService;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\EntityManagerInterface;
use Nelmio\ApiDocBundle\Annotation\Model;
use Nelmio\ApiDocBundle\Annotation\Security;
use OpenApi\Attributes as OA;
use App\Entity\Job;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

#[Route(path: '/api/v1/jobs', name: 'api_jobs')]
class ApiJobsController extends AbstractController
{

    #[Route(path: '', name: 'index', methods: ['GET'])]
    #[OA\Parameter(
        name: 'term',
        description: 'Search term',
        in: 'query',
        required: false,
        schema: new OA\Schema(type: 'string'),
    )]
    #[OA\Parameter(
        name: 'archive',
        description: 'Return or exclude archived jobs',
        in: 'query',
        required: false,
        schema: new OA\Schema(type: 'integer', enum: [0, 1]),
    )]
    #[OA\Parameter(
        name: 'location[]',
        description: 'Include only specific locations (both name or id are valid values)',
        in: 'query',
        required: false,
        schema: new OA\Schema(type: 'array', items: new OA\Items(type: 'string')),
    )]
    #[OA\Parameter(
        name: 'stint[]',
        description: 'Include only specific stints (both name or id are valid values)',
        in: 'query',
        required: false,
        schema: new OA\Schema(type: 'array', items: new OA\Items(type: 'string')),
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
        schema: new OA\Schema(type: 'string', enum: ['id', 'createdAt', 'updatedAt', 'applicationDeadline']),
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
        description: 'Returns all jobs',
        content: new OA\JsonContent(
            type: 'array',
            items: new OA\Items(ref: new Model(type: Job::class, groups: ['id', 'job']))
        )
    )]
    #[OA\Tag(name: 'Jobs')]
    public function index(Request $request, EntityManagerInterface $em,
                          NormalizerInterface $normalizer): JsonResponse
    {
        $qb = $em->createQueryBuilder();

        $qb
            ->select('e')
            ->from(Job::class, 'e')
        ;

        if($request->get('term')) {
            $qb
                ->andWhere('(e.name LIKE :term OR e.description LIKE :term OR e.translations LIKE :term)')
                ->setParameter('term', '%'.$request->get('term').'%');
        }
        
        if($request->get('archive')) {
            $qb
                ->andWhere('e.applicationDeadline < :applicationDeadline')
                ->setParameter('applicationDeadline', new \DateTime(), Types::DATETIME_MUTABLE)
            ;
        }

        if($request->get('archive') === '0') {
            $qb
                ->andWhere('(e.applicationDeadline IS NULL OR e.applicationDeadline >= :applicationDeadline)')
                ->setParameter('applicationDeadline', new \DateTime(), Types::DATETIME_MUTABLE)
            ;
        }

        if($request->get('location') && is_array($request->get('location')) && count($request->get('location'))) {
            foreach($request->get('location') as $key => $location) {
                $qb
                    ->leftJoin('e.locations', 'location'.$key)
                    ->andWhere('location'.$key.'.name = :location'.$key.' OR location'.$key.'.id = :locationId'.$key)
                    ->setParameter('location'.$key, $location)
                    ->setParameter('locationId'.$key, $location)
                ;
            }
        }

        if($request->get('stint') && is_array($request->get('stint')) && count($request->get('stint'))) {
            foreach($request->get('stint') as $key => $stint) {
                $qb
                    ->leftJoin('e.stints', 'stint'.$key)
                    ->andWhere('stint'.$key.'.name = :stint'.$key.' OR stint'.$key.'.id = :stintId'.$key)
                    ->setParameter('stint'.$key, $stint)
                    ->setParameter('stintId'.$key, $stint)
                ;
            }
        }

        if($request->get('limit')) {
            $qb->setMaxResults($request->get('limit'));
        }

        if($request->get('offset')) {
            $qb->setFirstResult($request->get('offset'));
        }

        if($request->get('orderBy') && is_array($request->get('orderBy')) && count($request->get('orderBy'))) {

            foreach($request->get('orderBy') as $key => $orderBy) {

                if(!in_array($orderBy, ['id', 'createdAt', 'updatedAt', 'applicationDeadline'])) {
                    continue;
                }

                $direction = 'ASC';

                if($request->get('orderDirection') && is_array($request->get('orderDirection')) &&
                    count($request->get('orderDirection')) && array_key_exists($key, $request->get('orderDirection')) &&
                    in_array($request->get('orderDirection')[$key], ['ASC', 'DESC'])) {
                    $direction = $request->get('orderDirection')[$key];
                }

                $qb
                    ->addOrderBy('e.'.$orderBy, $direction)
                ;

            }

        } else {
            $qb
                ->addOrderBy('e.position', 'ASC')
            ;
        }

        $jobs = $qb->getQuery()->getResult();

        $result = $normalizer->normalize($jobs, null, [
            'groups' => ['id', 'job', 'location'],
        ]);

        return $this->json($result);
    }
    
    #[Route(path: '/{id}', name: 'get', methods: ['GET'])]
    #[OA\Response(
        response: 200,
        description: 'Returns a single job',
        content: new OA\JsonContent(
            ref: new Model(type: Job::class, groups: ['id', 'job'])
        )
    )]
    #[OA\Tag(name: 'Jobs')]
    public function find(Request $request, EntityManagerInterface $em,
                         NormalizerInterface $normalizer): JsonResponse
    {
        $job = $em->getRepository(Job::class)
            ->find($request->get('id'));

        $result = $normalizer->normalize($job, null, [
            'groups' => ['id', 'job', 'location'],
        ]);

        return $this->json($result);
    }
    
    #[Route(path: '', name: 'create', methods: ['POST'])]
    #[IsGranted('ROLE_EDITOR')]
    #[OA\Response(
        response: 200,
        description: 'Create a job',
        content: new OA\JsonContent(
            ref: new Model(type: Job::class, groups: ['id', 'job'])
        )
    )]
    #[OA\Tag(name: 'Jobs')]
    #[Security(name: 'cookieAuth')]
    public function create(Request             $request, EntityManagerInterface $em,
                           NormalizerInterface $normalizer, JobService $jobService): JsonResponse
    {
        $payload = json_decode($request->getContent(), true);

        if(($errors = $jobService->validateJobPayload($payload)) !== true) {
            return $this->json($errors, 400);
        }

        $job = $jobService->createJob($payload);

        $result = $normalizer->normalize($job, null, [
            'groups' => ['id', 'job'],
        ]);

        return $this->json($result);
    }
    
    #[Route(path: '/{id}', name: 'update', methods: ['PUT'])]
    #[IsGranted('ROLE_EDITOR')]
    #[OA\Response(
        response: 200,
        description: 'Update a job',
        content: new OA\JsonContent(
            ref: new Model(type: Job::class, groups: ['id', 'job'])
        )
    )]
    #[OA\Tag(name: 'Jobs')]
    #[Security(name: 'cookieAuth')]
    public function update(Request             $request, EntityManagerInterface $em,
                           NormalizerInterface $normalizer, JobService $jobService): JsonResponse
    {
        $job = $em->getRepository(Job::class)
            ->find($request->get('id'));

        $payload = json_decode($request->getContent(), true);

        if(($errors = $jobService->validateJobPayload($payload)) !== true) {
            return $this->json($errors, 400);
        }

        $job = $jobService->updateJob($job, $payload);

        $result = $normalizer->normalize($job, null, [
            'groups' => ['id', 'job'],
        ]);

        return $this->json($result);
    }
    
    #[Route(path: '/{id}', name: 'delete', methods: ['DELETE'])]
    #[IsGranted('ROLE_EDITOR')]
    #[OA\Response(
        response: 200,
        description: 'Delete a job',
        content: new OA\JsonContent(
            type: 'array',
            items: new OA\Items(type: 'string'),
            default: []
        )
    )]
    #[OA\Tag(name: 'Jobs')]
    #[Security(name: 'cookieAuth')]
    public function delete(Request             $request, EntityManagerInterface $em,
                           NormalizerInterface $normalizer, JobService $jobService): JsonResponse
    {
        $job = $em->getRepository(Job::class)
            ->find($request->get('id'));

        $jobService->deleteJob($job);

        return $this->json([]);
    }

}