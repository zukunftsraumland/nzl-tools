<?php

namespace App\Controller;

use App\Entity\LEPeriod;
use Doctrine\ORM\EntityManagerInterface;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Nelmio\ApiDocBundle\Annotation\Security;

#[Route(path: '/api/v1/le-periods', name: 'api_le_periods_')]
class ApiLePeriodsController extends AbstractController
{
    /**
     * Get all LE Periods
     */
    #[Route(path: '', name: 'index', methods: ['GET'])]
    #[IsGranted('ROLE_EDITOR')]
    #[OA\Response(
        response: 200,
        description: 'Returns all LE Periods',
        content: new OA\JsonContent(
            type: 'array',
            items: new OA\Items(
                type: 'object',
                properties: [
                    new OA\Property(property: 'id', type: 'integer'),
                    new OA\Property(property: 'name', type: 'string')
                ]
            )
        )
    )]
    #[OA\Tag(name: 'LE Periods')]
    #[Security(name: 'cookieAuth')]
    public function index(EntityManagerInterface $em): JsonResponse
    {
        
        $lePeriods = $em->getRepository(LEPeriod::class)->findAll();
        
        $data = [];
        foreach ($lePeriods as $lePeriod) {
            $data[] = [
                'id' => $lePeriod->getId(),
                'name' => $lePeriod->getName()
            ];
        }
        
        return $this->json($data);
    }
    
    /**
     * Get a single LE Period
     */
    #[Route(path: '/{id}', name: 'get', methods: ['GET'])]
    #[IsGranted('ROLE_EDITOR')]
    #[OA\Response(
        response: 200,
        description: 'Returns a single LE Period',
        content: new OA\JsonContent(
            type: 'object',
            properties: [
                new OA\Property(property: 'id', type: 'integer'),
                new OA\Property(property: 'name', type: 'string')
            ]
        )
    )]
    #[OA\Tag(name: 'LE Periods')]
    #[Security(name: 'cookieAuth')]
    public function find(int $id, EntityManagerInterface $em): JsonResponse
    {
        $lePeriod = $em->getRepository(LEPeriod::class)->find($id);
        
        if (!$lePeriod) {
            return $this->json(['error' => 'LE Period not found'], 404);
        }
        
        return $this->json([
            'id' => $lePeriod->getId(),
            'name' => $lePeriod->getName()
        ]);
    }
} 