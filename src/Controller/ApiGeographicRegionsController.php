<?php

namespace App\Controller;

use App\Entity\GeographicRegion;
use Doctrine\ORM\EntityManagerInterface;
use Nelmio\ApiDocBundle\Annotation\Model;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

#[Route(path: '/api/v1/geographic-regions', name: 'api_geographic-regions_')]
class ApiGeographicRegionsController extends AbstractController
{
    
    #[Route(path: '', name: 'index', methods: ['GET'])]
    #[OA\Response(
        response: 200,
        description: 'Returns all geographic regions',
        content: new OA\JsonContent(
            type: 'array',
            items: new OA\Items(ref: new Model(type: GeographicRegion::class, groups: ['id', 'geographic_region']))
        )
    )]
    #[OA\Tag(name: 'Geographic Regions')]
    public function index(EntityManagerInterface $em, NormalizerInterface $normalizer): JsonResponse
    {
        $geographicRegions = $em->getRepository(GeographicRegion::class)->findBy([
            //'isPublic' => 1
        ], [], 10000);

        $result = $normalizer->normalize($geographicRegions, null, [
            'groups' => ['id', 'geographic_region'],
        ]);

        return $this->json($result);
    }

    #[Route(path: '/{id}', name: 'get', methods: ['GET'])]
    #[OA\Response(
        response: 200,
        description: 'Returns a single geographic region',
        content: new OA\JsonContent(
            ref: new Model(type: GeographicRegion::class, groups: ['id', 'geographic_region'])
        )
    )]
    #[OA\Tag(name: 'Geographic Regions')]
    public function find(Request $request, EntityManagerInterface $em, NormalizerInterface $normalizer): JsonResponse
    {
        $geographicRegion = $em->getRepository(GeographicRegion::class)
            ->find($request->get('id'));

        $result = $normalizer->normalize($geographicRegion, null, [
            'groups' => ['id', 'geographic_region'],
        ]);

        return $this->json($result);
    }

}