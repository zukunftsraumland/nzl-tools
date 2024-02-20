<?php

namespace App\Controller;

use App\Service\EducationService;
use Doctrine\ORM\EntityManagerInterface;
use Nelmio\ApiDocBundle\Annotation\Model;
use Nelmio\ApiDocBundle\Annotation\Security;
use OpenApi\Attributes as OA;
use App\Entity\Education;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

#[Route(path: '/api/v1/educations', name: 'api_educations_')]
class ApiEducationsController extends AbstractController
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
        name: 'educationType[]',
        description: 'Include only specific education types (both name or id are valid values)',
        in: 'query',
        required: false,
        schema: new OA\Schema(type: 'array', items: new OA\Items(type: 'string')),
    )]
    #[OA\Parameter(
        name: 'language[]',
        description: 'Include only specific languages (both name or id are valid values)',
        in: 'query',
        required: false,
        schema: new OA\Schema(type: 'array', items: new OA\Items(type: 'string')),
    )]
    #[OA\Parameter(
        name: 'location[]',
        description: 'Include only specific locations (both name or id are valid values)',
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
        description: 'Returns all educations',
        content: new OA\JsonContent(
            type: 'array',
            items: new OA\Items(ref: new Model(type: Education::class, groups: ['id', 'education']))
        )
    )]
    #[OA\Tag(name: 'Educations')]
    public function index(Request $request, EntityManagerInterface $em,
                          NormalizerInterface $normalizer): JsonResponse
    {
        $qb = $em->createQueryBuilder();

        $qb
            ->select('e')
            ->from(Education::class, 'e')
        ;

        if($request->get('term')) {
            $qb
                ->andWhere('(e.name LIKE :term OR e.description LIKE :term OR e.text LIKE :term OR e.translations LIKE :term)')
                ->setParameter('term', '%'.$request->get('term').'%');
        }

        if($request->get('language') && is_array($request->get('language')) && count($request->get('language'))) {
            foreach($request->get('language') as $key => $language) {
                $qb
                    ->leftJoin('e.languages', 'language'.$key)
                    ->andWhere('language'.$key.'.name = :language'.$key.' OR language'.$key.'.id = :languageId'.$key)
                    ->setParameter('language'.$key, $language)
                    ->setParameter('languageId'.$key, $language)
                ;
            }
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

        if($request->get('educationType') && is_array($request->get('educationType')) && count($request->get('educationType'))) {
            foreach($request->get('educationType') as $key => $educationType) {
                $qb
                    ->leftJoin('e.educationTypes', 'educationType'.$key)
                    ->andWhere('educationType'.$key.'.name = :educationType'.$key.' OR educationType'.$key.'.id = :educationTypeId'.$key)
                    ->setParameter('educationType'.$key, $educationType)
                    ->setParameter('educationTypeId'.$key, $educationType)
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
                    ->addOrderBy('e.'.$orderBy, $direction)
                ;

            }

        } else {
            $qb
                ->addOrderBy('e.name', 'ASC')
            ;
        }

        $educations = $qb->getQuery()->getResult();

        $result = $normalizer->normalize($educations, null, [
            'groups' => ['id', 'education', 'education_type', 'language', 'location'],
        ]);

        return $this->json($result);
    }
    
    #[Route(path: '/{id}', name: 'get', methods: ['GET'])]
    #[OA\Response(
        response: 200,
        description: 'Returns a single education',
        content: new OA\JsonContent(
            ref: new Model(type: Education::class, groups: ['id', 'education'])
        )
    )]
    #[OA\Tag(name: 'Educations')]
    public function find(Request $request, EntityManagerInterface $em,
                         NormalizerInterface $normalizer): JsonResponse
    {
        $education = $em->getRepository(Education::class)
            ->find($request->get('id'));

        $result = $normalizer->normalize($education, null, [
            'groups' => ['id', 'education', 'education_type', 'language', 'location'],
        ]);

        return $this->json($result);
    }
    
    #[Route(path: '', name: 'create', methods: ['POST'])]
    #[IsGranted('ROLE_EDITOR')]
    #[OA\Response(
        response: 200,
        description: 'Create an education',
        content: new OA\JsonContent(
            ref: new Model(type: Education::class, groups: ['id', 'education'])
        )
    )]
    #[OA\Tag(name: 'Educations')]
    #[Security(name: 'cookieAuth')]
    public function create(Request $request, EntityManagerInterface $em,
                           NormalizerInterface $normalizer, EducationService $educationService): JsonResponse
    {
        $payload = json_decode($request->getContent(), true);

        if(($errors = $educationService->validateEducationPayload($payload)) !== true) {
            return $this->json($errors, 400);
        }

        $education = $educationService->createEducation($payload);

        $result = $normalizer->normalize($education, null, [
            'groups' => ['id', 'education'],
        ]);

        return $this->json($result);
    }
    
    #[Route(path: '/{id}', name: 'update', methods: ['PUT'])]
    #[IsGranted('ROLE_EDITOR')]
    #[OA\Response(
        response: 200,
        description: 'Update an education',
        content: new OA\JsonContent(
            ref: new Model(type: Education::class, groups: ['id', 'education'])
        )
    )]
    #[OA\Tag(name: 'Educations')]
    #[Security(name: 'cookieAuth')]
    public function update(Request $request, EntityManagerInterface $em,
                           NormalizerInterface $normalizer, EducationService $educationService): JsonResponse
    {
        $education = $em->getRepository(Education::class)
            ->find($request->get('id'));

        $payload = json_decode($request->getContent(), true);

        if(($errors = $educationService->validateEducationPayload($payload)) !== true) {
            return $this->json($errors, 400);
        }

        $education = $educationService->updateEducation($education, $payload);

        $result = $normalizer->normalize($education, null, [
            'groups' => ['id', 'education'],
        ]);

        return $this->json($result);
    }
    
    #[Route(path: '/{id}', name: 'delete', methods: ['DELETE'])]
    #[IsGranted('ROLE_EDITOR')]
    #[OA\Response(
        response: 200,
        description: 'Delete an education',
        content: new OA\JsonContent(
            type: 'array',
            items: new OA\Items(type: 'string'),
            default: []
        )
    )]
    #[OA\Tag(name: 'Educations')]
    #[Security(name: 'cookieAuth')]
    public function delete(Request $request, EntityManagerInterface $em,
                           NormalizerInterface $normalizer, EducationService $educationService): JsonResponse
    {
        $education = $em->getRepository(Education::class)
            ->find($request->get('id'));

        $educationService->deleteEducation($education);

        return $this->json([]);
    }
    
}