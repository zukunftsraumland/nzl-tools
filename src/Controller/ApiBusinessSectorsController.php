<?php

namespace App\Controller;

use App\Entity\BusinessSector;
use Doctrine\ORM\EntityManagerInterface;
use Nelmio\ApiDocBundle\Annotation\Model;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

#[Route(path: '/api/v1/business-sectors', name: 'api_business-sectors_')]
class ApiBusinessSectorsController extends AbstractController
{
    
    #[Route(path: '', name: 'index', methods: ['GET'])]
    #[OA\Response(
        response: 200,
        description: 'Returns all business sectors',
        content: new OA\JsonContent(
            type: 'array',
            items: new OA\Items(ref: new Model(type: BusinessSector::class, groups: ['id', 'business_sector']))
        )
    )]
    #[OA\Tag(name: 'Business Sectors')]
    public function index(EntityManagerInterface $em, NormalizerInterface $normalizer): JsonResponse
    {
        $businessSectors = $em->getRepository(BusinessSector::class)->findBy([
            //'isPublic' => 1
        ], [], 10000);

        $result = $normalizer->normalize($businessSectors, null, [
            'groups' => ['id', 'business_sector'],
        ]);

        return $this->json($result);
    }

    #[Route(path: '/{id}', name: 'get', methods: ['GET'])]
    #[OA\Response(
        response: 200,
        description: 'Returns a single business sector',
        content: new OA\JsonContent(
            ref: new Model(type: BusinessSector::class, groups: ['id', 'business_sector'])
        )
    )]
    #[OA\Tag(name: 'Business Sectors')]
    public function find(Request $request, EntityManagerInterface $em, NormalizerInterface $normalizer): JsonResponse
    {
        $businessSector = $em->getRepository(BusinessSector::class)
            ->find($request->get('id'));

        $result = $normalizer->normalize($businessSector, null, [
            'groups' => ['id', 'business_sector'],
        ]);

        return $this->json($result);
    }

}