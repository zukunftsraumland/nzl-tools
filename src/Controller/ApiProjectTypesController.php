<?php

namespace App\Controller;

use App\Entity\ProjectType;
use Doctrine\ORM\EntityManagerInterface;
use Nelmio\ApiDocBundle\Annotation\Model;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

#[Route(path: '/api/v1/project-types', name: 'api_project_types_')]
class ApiProjectTypesController extends AbstractController
{

    #[Route(path: '', name: 'index', methods: ['GET'])]
    #[OA\Response(
        response: 200,
        description: 'Returns all project types',
        content: new OA\JsonContent(
            type: 'array',
            items: new OA\Items(ref: new Model(type: ProjectType::class, groups: ['id', 'project_type']))
        )
    )]
    #[OA\Tag(name: 'ProjectTypes')]
    public function index(EntityManagerInterface $em, NormalizerInterface $normalizer): JsonResponse
    {
        $projectTypes = $em->getRepository(ProjectType::class)->findBy([
            //'isPublic' => 1
        ], [], 10000);

        $result = $normalizer->normalize($projectTypes, null, [
            'groups' => ['id', 'project_type'],
        ]);

        return $this->json($result);
    }

    #[Route(path: '/{id}', name: 'get', methods: ['GET'])]
    #[OA\Response(
        response: 200,
        description: 'Returns a single project type',
        content: new OA\JsonContent(
            ref: new Model(type: ProjectType::class, groups: ['id', 'project_type'])
        )
    )]
    #[OA\Tag(name: 'ProjectTypes')]
    public function find(Request $request, EntityManagerInterface $em, NormalizerInterface $normalizer): JsonResponse
    {
        $projectType = $em->getRepository(ProjectType::class)
            ->find($request->get('id'));

        $result = $normalizer->normalize($projectType, null, [
            'groups' => ['id', 'project_type'],
        ]);

        return $this->json($result);
    }

}