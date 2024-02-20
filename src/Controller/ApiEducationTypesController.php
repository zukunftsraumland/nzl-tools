<?php

namespace App\Controller;

use App\Entity\EducationType;
use Doctrine\ORM\EntityManagerInterface;
use Nelmio\ApiDocBundle\Annotation\Model;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

#[Route(path: '/api/v1/education-types', name: 'api_education_types')]
class ApiEducationTypesController extends AbstractController
{

    #[Route(path: '', name: 'index', methods: ['GET'])]
    #[OA\Response(
        response: 200,
        description: 'Returns all education types',
        content: new OA\JsonContent(
            type: 'array',
            items: new OA\Items(ref: new Model(type: EducationType::class, groups: ['id', 'education_type']))
        )
    )]
    #[OA\Tag(name: 'EducationTypes')]
    public function index(EntityManagerInterface $em, NormalizerInterface $normalizer): JsonResponse
    {
        $educationTypes = $em->getRepository(EducationType::class)->findBy([
            //'isPublic' => 1
        ], [], 10000);

        $result = $normalizer->normalize($educationTypes, null, [
            'groups' => ['id', 'education_type'],
        ]);

        return $this->json($result);
    }

    #[Route(path: '/{id}', name: 'get', methods: ['GET'])]
    #[OA\Response(
        response: 200,
        description: 'Returns a single education type',
        content: new OA\JsonContent(
            ref: new Model(type: EducationType::class, groups: ['id', 'education_type'])
        )
    )]
    #[OA\Tag(name: 'EducationTypes')]
    public function find(Request $request, EntityManagerInterface $em, NormalizerInterface $normalizer): JsonResponse
    {
        $educationType = $em->getRepository(EducationType::class)
            ->find($request->get('id'));

        $result = $normalizer->normalize($educationType, null, [
            'groups' => ['id', 'education_type'],
        ]);

        return $this->json($result);
    }

}