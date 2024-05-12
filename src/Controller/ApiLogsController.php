<?php

namespace App\Controller;

use App\Service\LogService;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\EntityManagerInterface;
use Nelmio\ApiDocBundle\Annotation\Model;
use Nelmio\ApiDocBundle\Annotation\Security;
use OpenApi\Attributes as OA;
use App\Entity\Log;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

#[Route(path: '/api/v1/logs', name: 'api_logs_')]
class ApiLogsController extends AbstractController
{

    #[Route(path: '/pixel', name: 'pixel', methods: ['GET'])]
    #[OA\Parameter(
        name: 'd',
        description: 'Base64 encoded telemetry data',
        in: 'query',
        required: true,
        schema: new OA\Schema(type: 'string'),
    )]
    #[OA\Response(
        response: 200,
        description: 'Returns a transparent gif',
        content: new OA\MediaType(mediaType: 'image/gif', schema: new OA\Schema(type: 'string', format: 'binary'))
    )]
    #[OA\Tag(name: 'Logs')]
    public function pixel(Request $request, LogService $logService): Response
    {
        $payload = json_decode($request->get('d'), true);

        $response = new Response();
        $response->setContent(base64_decode('R0lGODlhAQABAJAAAP8AAAAAACH5BAUQAAAALAAAAAABAAEAAAICBAEAOw=='));
        $response->headers->set('Content-Type', 'image/gif');
        $response->headers->addCacheControlDirective('no-cache');
        $response->headers->addCacheControlDirective('must-revalidate');
        $response->setPrivate();

        if(($errors = $logService->validateTelemetryPayload($payload)) !== true) {
            $response->setStatusCode(400);

            return $response;
        }

        $logService->createTelemetry($payload);

        return $response;
    }
    
    #[Route(path: '/telemetry', name: 'telemetry', methods: ['POST'])]
    #[OA\Response(
        response: 200,
        description: 'Returns an empty array',
        content: new OA\JsonContent(
            type: 'array',
            items: new OA\Items(type: 'string'),
            default: []
        )
    )]
    #[OA\Tag(name: 'Logs')]
    public function telemetry(Request $request, LogService $logService): Response
    {
        $payload = json_decode($request->getContent(), true);

        if(($errors = $logService->validateTelemetryPayload($payload)) !== true) {
            return $this->json($errors, 400);
        }

        $logService->createTelemetry($payload);

        return $this->json([]);
    }

    #[Route(path: '', name: 'index', methods: ['GET'])]
    #[IsGranted('ROLE_SUPER_ADMIN')]
    #[OA\Parameter(
        name: 'term',
        description: 'Search term',
        in: 'query',
        required: false,
        schema: new OA\Schema(type: 'string'),
    )]
    #[OA\Parameter(
        name: 'type[]',
        description: 'Include only specific log types',
        in: 'query',
        required: false,
        schema: new OA\Schema(type: 'array', items: new OA\Items(type: 'string')),
    )]
    #[OA\Parameter(
        name: 'context[]',
        description: 'Include only specific log contexts',
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
        description: 'Returns all logs',
        content: new OA\JsonContent(
            type: 'array',
            items: new OA\Items(ref: new Model(type: Log::class, groups: ['id', 'log']))
        )
    )]
    #[OA\Tag(name: 'Logs')]
    #[Security(name: 'cookieAuth')]
    public function index(Request $request, EntityManagerInterface $em,
                          NormalizerInterface $normalizer): JsonResponse
    {
        $qb = $em->createQueryBuilder();

        $qb
            ->select('e')
            ->from(Log::class, 'e')
        ;

        if($request->get('term')) {
            $qb
                ->andWhere('(e.message LIKE :term OR e.data LIKE :term OR e.message LIKE :term OR e.meta LIKE :term)')
                ->setParameter('term', '%'.$request->get('term').'%');
        }

        if($request->get('type') && !is_array($request->get('type'))) {
            $qb
                ->andWhere('e.type = :type')
                ->setParameter('type', $request->get('type'))
            ;
        }

        if($request->get('type') && is_array($request->get('type'))) {
            $qb
                ->andWhere('e.type IN (:types)')
                ->setParameter('types', $request->get('type'))
            ;
        }

        if($request->get('context') && !is_array($request->get('context'))) {
            $qb
                ->andWhere('e.context = :context')
                ->setParameter('context', $request->get('context'))
            ;
        }

        if($request->get('context') && is_array($request->get('context'))) {
            $qb
                ->andWhere('e.context IN (:contexts)')
                ->setParameter('contexts', $request->get('context'))
            ;
        }

        if($request->get('limit')) {
            $qb->setMaxResults($request->get('limit'));
        }

        if($request->get('offset')) {
            $qb->setFirstResult($request->get('offset'));
        }

        if($request->get('orderBy') && is_array($request->get('orderBy')) && count($request->get('orderBy'))) {

            foreach($request->get('orderBy') as $key => $orderBy) {

                if(!in_array($orderBy, ['id', 'createdAt'])) {
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
                ->addOrderBy('e.id', 'DESC')
            ;
        }

        $logs = $qb->getQuery()->getResult();

        $result = $normalizer->normalize($logs, null, [
            'groups' => ['id', 'log'],
        ]);

        return $this->json($result);
    }
    
    #[Route(path: '/{id}', name: 'get', methods: ['GET'])]
    #[IsGranted('ROLE_SUPER_ADMIN')]
    #[OA\Response(
        response: 200,
        description: 'Returns a single log',
        content: new OA\JsonContent(
            ref: new Model(type: Log::class, groups: ['id', 'log'])
        )
    )]
    #[OA\Tag(name: 'Logs')]
    #[Security(name: 'cookieAuth')]
    public function find(Request $request, EntityManagerInterface $em,
                         NormalizerInterface $normalizer): JsonResponse
    {
        $log = $em->getRepository(Log::class)
            ->find($request->get('id'));

        $result = $normalizer->normalize($log, null, [
            'groups' => ['id', 'log'],
        ]);

        return $this->json($result);
    }
    
    #[Route(path: '', name: 'create', methods: ['POST'])]
    #[IsGranted('ROLE_SUPER_ADMIN')]
    #[OA\Response(
        response: 200,
        description: 'Create a log',
        content: new OA\JsonContent(
            ref: new Model(type: Log::class, groups: ['id', 'log'])
        )
    )]
    #[OA\Tag(name: 'Logs')]
    #[Security(name: 'cookieAuth')]
    public function create(Request $request, NormalizerInterface $normalizer, LogService $logService): JsonResponse
    {
        $payload = json_decode($request->getContent(), true);

        if(($errors = $logService->validateLogPayload($payload)) !== true) {
            return $this->json($errors, 400);
        }

        $log = $logService->createLog($payload);

        $result = $normalizer->normalize($log, null, [
            'groups' => ['id', 'log'],
        ]);

        return $this->json($result);
    }
    
    #[Route(path: '/{id}', name: 'update', methods: ['PUT'])]
    #[IsGranted('ROLE_SUPER_ADMIN')]
    #[OA\Response(
        response: 200,
        description: 'Update a log',
        content: new OA\JsonContent(
            ref: new Model(type: Log::class, groups: ['id', 'log'])
        )
    )]
    #[OA\Tag(name: 'Logs')]
    #[Security(name: 'cookieAuth')]
    public function update(Request $request, EntityManagerInterface $em,
                           NormalizerInterface $normalizer, LogService $logService): JsonResponse
    {
        $log = $em->getRepository(Log::class)
            ->find($request->get('id'));

        $payload = json_decode($request->getContent(), true);

        if(($errors = $logService->validateLogPayload($payload)) !== true) {
            return $this->json($errors, 400);
        }

        $log = $logService->updateLog($log, $payload);

        $result = $normalizer->normalize($log, null, [
            'groups' => ['id', 'log'],
        ]);

        return $this->json($result);
    }
    
    #[Route(path: '/{id}', name: 'delete', methods: ['DELETE'])]
    #[IsGranted('ROLE_SUPER_ADMIN')]
    #[OA\Response(
        response: 200,
        description: 'Delete a log',
        content: new OA\JsonContent(
            type: 'array',
            items: new OA\Items(type: 'string'),
            default: []
        )
    )]
    #[OA\Tag(name: 'Logs')]
    #[Security(name: 'cookieAuth')]
    public function delete(Request $request, EntityManagerInterface $em, LogService $logService): JsonResponse
    {
        $log = $em->getRepository(Log::class)
            ->find($request->get('id'));

        $logService->deleteLog($log);

        return $this->json([]);
    }
}