<?php

namespace App\Controller;

use App\Entity\Location;
use Doctrine\ORM\EntityManagerInterface;
use Nelmio\ApiDocBundle\Annotation\Model;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

#[Route(path: '/api/v1/locations', name: 'api_locations_')]
class ApiLocationsController extends AbstractController
{

    #[Route(path: '', name: 'index', methods: ['GET'])]
    #[OA\Response(
        response: 200,
        description: 'Returns all locations',
        content: new OA\JsonContent(
            type: 'array',
            items: new OA\Items(ref: new Model(type: Location::class, groups: ['id', 'location']))
        )
    )]
    #[OA\Tag(name: 'Locations')]
    public function index(EntityManagerInterface $em, NormalizerInterface $normalizer): JsonResponse
    {
        $locations = $em->getRepository(Location::class)->findBy([
            //'isPublic' => 1
        ], [], 10000);

        $result = $normalizer->normalize($locations, null, [
            'groups' => ['id', 'location'],
        ]);

        return $this->json($result);
    }

    #[Route(path: '/{id}', name: 'get', methods: ['GET'])]
    #[OA\Response(
        response: 200,
        description: 'Returns a single location',
        content: new OA\JsonContent(
            ref: new Model(type: Location::class, groups: ['id', 'location'])
        )
    )]
    #[OA\Tag(name: 'Locations')]
    public function find(Request $request, EntityManagerInterface $em, NormalizerInterface $normalizer): JsonResponse
    {
        $location = $em->getRepository(Location::class)
            ->find($request->get('id'));

        $result = $normalizer->normalize($location, null, [
            'groups' => ['id', 'location'],
        ]);

        return $this->json($result);
    }

}