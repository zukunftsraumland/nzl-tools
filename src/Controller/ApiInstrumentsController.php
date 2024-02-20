<?php

namespace App\Controller;

use App\Entity\Instrument;
use Doctrine\ORM\EntityManagerInterface;
use Nelmio\ApiDocBundle\Annotation\Model;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

#[Route(path: '/api/v1/instruments', name: 'api_instruments_')]
class ApiInstrumentsController extends AbstractController
{
    
    #[Route(path: '', name: 'index', methods: ['GET'])]
    #[OA\Response(
        response: 200,
        description: 'Returns all instruments',
        content: new OA\JsonContent(
            type: 'array',
            items: new OA\Items(ref: new Model(type: Instrument::class, groups: ['id', 'instrument']))
        )
    )]
    #[OA\Tag(name: 'Instruments')]
    public function index(EntityManagerInterface $em, NormalizerInterface $normalizer): JsonResponse
    {
        $instruments = $em->getRepository(Instrument::class)->findBy([
            //'isPublic' => 1
        ], [], 10000);

        $result = $normalizer->normalize($instruments, null, [
            'groups' => ['id', 'instrument'],
        ]);

        return $this->json($result);
    }
    
    #[Route(path: '/{id}', name: 'get', methods: ['GET'])]
    #[OA\Response(
        response: 200,
        description: 'Returns a single instrument',
        content: new OA\JsonContent(
            ref: new Model(type: Instrument::class, groups: ['id', 'instrument'])
        )
    )]
    #[OA\Tag(name: 'Instruments')]
    public function find(Request $request, EntityManagerInterface $em, NormalizerInterface $normalizer): JsonResponse
    {
        $instrument = $em->getRepository(Instrument::class)
            ->find($request->get('id'));

        $result = $normalizer->normalize($instrument, null, [
            'groups' => ['id', 'instrument'],
        ]);

        return $this->json($result);
    }
    
}