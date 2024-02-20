<?php

namespace App\Controller;

use App\Entity\Stint;
use Doctrine\ORM\EntityManagerInterface;
use Nelmio\ApiDocBundle\Annotation\Model;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

#[Route(path: '/api/v1/stints', name: 'api_stints_')]
class ApiStintsController extends AbstractController
{

    #[Route(path: '', name: 'index', methods: ['GET'])]
    #[OA\Response(
        response: 200,
        description: 'Returns all stints',
        content: new OA\JsonContent(
            type: 'array',
            items: new OA\Items(ref: new Model(type: Stint::class, groups: ['id', 'stint']))
        )
    )]
    #[OA\Tag(name: 'Stints')]
    public function index(EntityManagerInterface $em, NormalizerInterface $normalizer): JsonResponse
    {
        $stints = $em->getRepository(Stint::class)->findBy([
            //'isPublic' => 1
        ], [], 10000);

        $result = $normalizer->normalize($stints, null, [
            'groups' => ['id', 'stint'],
        ]);

        return $this->json($result);
    }

    #[Route(path: '/{id}', name: 'get', methods: ['GET'])]
    #[OA\Response(
        response: 200,
        description: 'Returns a single stint',
        content: new OA\JsonContent(
            ref: new Model(type: Stint::class, groups: ['id', 'stint'])
        )
    )]
    #[OA\Tag(name: 'Stints')]
    public function find(Request $request, EntityManagerInterface $em, NormalizerInterface $normalizer): JsonResponse
    {
        $stint = $em->getRepository(Stint::class)
            ->find($request->get('id'));

        $result = $normalizer->normalize($stint, null, [
            'groups' => ['id', 'stint'],
        ]);

        return $this->json($result);
    }

}