<?php

namespace App\Controller;

use App\Entity\City;
use Doctrine\ORM\EntityManagerInterface;
use Nelmio\ApiDocBundle\Annotation\Model;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

#[Route(path: '/api/v1/cities', name: 'api_cities_')]
class ApiCitiesController extends AbstractController
{

    #[Route(path: '', name: 'index', methods: ['GET'])]
    #[OA\Response(
        response: 200,
        description: 'Returns all cities',
        content: new OA\JsonContent(
            type: 'array',
            items: new OA\Items(ref: new Model(type: City::class, groups: ['id', 'city']))
        )
    )]
    #[OA\Tag(name: 'Cities')]
    public function index(EntityManagerInterface $em, NormalizerInterface $normalizer): JsonResponse
    {
        $cities = $em->getRepository(City::class)->findBy([
            //'isPublic' => 1
        ], [], 10000);

        $result = $normalizer->normalize($cities, null, [
            'groups' => ['id', 'city'],
        ]);

        return $this->json($result);
    }

    #[Route(path: '/{id}', name: 'get', methods: ['GET'])]
    #[OA\Response(
        response: 200,
        description: 'Returns a single city',
        content: new OA\JsonContent(
            ref: new Model(type: City::class, groups: ['id', 'city'])
        )
    )]
    #[OA\Tag(name: 'Cities')]
    public function find(Request $request, EntityManagerInterface $em, NormalizerInterface $normalizer): JsonResponse
    {
        $city = $em->getRepository(City::class)
            ->find($request->get('id'));

        $result = $normalizer->normalize($city, null, [
            'groups' => ['id', 'city'],
        ]);

        return $this->json($result);
    }

}