<?php

namespace App\Controller;

use App\Service\EmploymentService;
use Doctrine\ORM\EntityManagerInterface;
use Nelmio\ApiDocBundle\Annotation\Model;
use Nelmio\ApiDocBundle\Annotation\Security;
use OpenApi\Attributes as OA;
use App\Entity\Employment;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

#[Route(path: '/api/v1/employments', name: 'api_employments_')]
class ApiEmploymentsController extends AbstractController
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
        description: 'Returns all employments',
        content: new OA\JsonContent(
            type: 'array',
            items: new OA\Items(ref: new Model(type: Employment::class, groups: ['id', 'employment']))
        )
    )]
    #[OA\Tag(name: 'Employments')]
    public function index(Request $request, EntityManagerInterface $em,
                          NormalizerInterface $normalizer): JsonResponse
    {
        $qb = $em->createQueryBuilder();

        $qb
            ->select('c')
            ->from(Employment::class, 'c')
        ;

        if($request->get('term')) {
            $qb
                ->andWhere('(c.role LIKE :term)')
                ->setParameter('term', '%'.$request->get('term').'%');
        }

        if($request->get('limit')) {
            $qb->setMaxResults($request->get('limit'));
        }

        if($request->get('offset')) {
            $qb->setFirstResult($request->get('offset'));
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
                    ->addOrderBy('c.'.$orderBy, $direction)
                ;

            }

        } else {
            $qb
                ->addOrderBy('c.id', 'ASC')
            ;
        }

        $employments = $qb->getQuery()->getResult();

        $result = $normalizer->normalize($employments, null, [
            'groups' => ['id', 'employment'],
        ]);

        return $this->json($result);
    }
    
    #[Route(path: '/{id}', name: 'get', methods: ['GET'])]
    #[OA\Response(
        response: 200,
        description: 'Returns a single employment',
        content: new OA\JsonContent(
            ref: new Model(type: Employment::class, groups: ['id', 'employment'])
        )
    )]
    #[OA\Tag(name: 'Employments')]
    public function find(Request $request, EntityManagerInterface $em,
                         NormalizerInterface $normalizer): JsonResponse
    {
        $employment = $em->getRepository(Employment::class)
            ->find($request->get('id'));

        $result = $normalizer->normalize($employment, null, [
            'groups' => ['id', 'employment'],
        ]);

        return $this->json($result);
    }
    
    #[Route(path: '', name: 'create', methods: ['POST'])]
    #[IsGranted('ROLE_EDITOR')]
    #[OA\Response(
        response: 200,
        description: 'Create an employment',
        content: new OA\JsonContent(
            ref: new Model(type: Employment::class, groups: ['id', 'employment'])
        )
    )]
    #[OA\Tag(name: 'Employments')]
    #[Security(name: 'cookieAuth')]
    public function create(Request $request, EntityManagerInterface $em,
                           NormalizerInterface $normalizer, EmploymentService $employmentService): JsonResponse
    {
        $payload = json_decode($request->getContent(), true);

        if(($errors = $employmentService->validateEmploymentPayload($payload)) !== true) {
            return $this->json($errors, 400);
        }

        $employment = $employmentService->createEmployment($payload);

        $result = $normalizer->normalize($employment, null, [
            'groups' => ['id', 'employment'],
        ]);

        return $this->json($result);
    }
    
    #[Route(path: '/{id}', name: 'update', methods: ['PUT'])]
    #[IsGranted('ROLE_EDITOR')]
    #[OA\Response(
        response: 200,
        description: 'Update an employment',
        content: new OA\JsonContent(
            ref: new Model(type: Employment::class, groups: ['id', 'employment'])
        )
    )]
    #[OA\Tag(name: 'Employments')]
    #[Security(name: 'cookieAuth')]
    public function update(Request $request, EntityManagerInterface $em,
                           NormalizerInterface $normalizer, EmploymentService $employmentService): JsonResponse
    {
        $employment = $em->getRepository(Employment::class)
            ->find($request->get('id'));

        $payload = json_decode($request->getContent(), true);

        if(($errors = $employmentService->validateEmploymentPayload($payload)) !== true) {
            return $this->json($errors, 400);
        }

        $employment = $employmentService->updateEmployment($employment, $payload);

        $result = $normalizer->normalize($employment, null, [
            'groups' => ['id', 'employment'],
        ]);

        return $this->json($result);
    }
    
    #[Route(path: '/{id}', name: 'delete', methods: ['DELETE'])]
    #[IsGranted('ROLE_EDITOR')]
    #[OA\Response(
        response: 200,
        description: 'Delete an employment',
        content: new OA\JsonContent(
            type: 'array',
            items: new OA\Items(type: 'string'),
            default: []
        )
    )]
    #[OA\Tag(name: 'Employments')]
    #[Security(name: 'cookieAuth')]
    public function delete(Request $request, EntityManagerInterface $em,
                           NormalizerInterface $normalizer, EmploymentService $employmentService): JsonResponse
    {
        $employment = $em->getRepository(Employment::class)
            ->find($request->get('id'));

        $employmentService->deleteEmployment($employment);

        return $this->json([]);
    }
    
}