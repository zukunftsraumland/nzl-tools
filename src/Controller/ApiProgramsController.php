<?php

namespace App\Controller;

use App\Entity\Program;
use Doctrine\ORM\EntityManagerInterface;
use Nelmio\ApiDocBundle\Annotation\Model;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

#[Route(path: '/api/v1/programs', name: 'api_programs_')]
class ApiProgramsController extends AbstractController
{

    #[Route(path: '', name: 'index', methods: ['GET'])]
    #[OA\Response(
        response: 200,
        description: 'Returns all programs',
        content: new OA\JsonContent(
            type: 'array',
            items: new OA\Items(ref: new Model(type: Program::class, groups: ['id', 'program']))
        )
    )]
    #[OA\Tag(name: 'Programs')]
    public function index(EntityManagerInterface $em, NormalizerInterface $normalizer): JsonResponse
    {
        $programs = $em->getRepository(Program::class)->findBy([
            //'isPublic' => 1
        ], [], 10000);

        $result = $normalizer->normalize($programs, null, [
            'groups' => ['id', 'program'],
        ]);

        return $this->json($result);
    }

    #[Route(path: '/{id}', name: 'get', methods: ['GET'])]
    #[OA\Response(
        response: 200,
        description: 'Returns a single program',
        content: new OA\JsonContent(
            ref: new Model(type: Program::class, groups: ['id', 'program'])
        )
    )]
    #[OA\Tag(name: 'Programs')]
    public function find(Request $request, EntityManagerInterface $em, NormalizerInterface $normalizer): JsonResponse
    {
        $program = $em->getRepository(Program::class)
            ->find($request->get('id'));

        $result = $normalizer->normalize($program, null, [
            'groups' => ['id', 'program'],
        ]);

        return $this->json($result);
    }

}