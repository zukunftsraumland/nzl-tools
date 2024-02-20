<?php

namespace App\Controller;

use App\Entity\Authority;
use Doctrine\ORM\EntityManagerInterface;
use Nelmio\ApiDocBundle\Annotation\Model;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

#[Route(path: '/api/v1/authorities', name: 'api_authorities_')]
class ApiAuthoritiesController extends AbstractController
{

    #[Route(path: '', name: 'index', methods: ['GET'])]
    #[OA\Response(
        response: 200,
        description: 'Returns all authorities',
        content: new OA\JsonContent(
            type: 'array',
            items: new OA\Items(ref: new Model(type: Authority::class, groups: ['id', 'authority']))
        )
    )]
    #[OA\Tag(name: 'Authorities')]
    public function index(EntityManagerInterface $em, NormalizerInterface $normalizer): JsonResponse
    {
        $authorities = $em->getRepository(Authority::class)->findBy([
            //'isPublic' => 1
        ], [], 10000);

        $result = $normalizer->normalize($authorities, null, [
            'groups' => ['id', 'authority'],
        ]);

        return $this->json($result);
    }

    #[Route(path: '/{id}', name: 'get', methods: ['GET'])]
    #[OA\Response(
        response: 200,
        description: 'Returns a single authority',
        content: new OA\JsonContent(
            ref: new Model(type: Authority::class, groups: ['id', 'authority'])
        )
    )]
    #[OA\Tag(name: 'Authorities')]
    public function find(Request $request, EntityManagerInterface $em, NormalizerInterface $normalizer): JsonResponse
    {
        $authority = $em->getRepository(Authority::class)
            ->find($request->get('id'));

        $result = $normalizer->normalize($authority, null, [
            'groups' => ['id', 'authority'],
        ]);

        return $this->json($result);
    }

}