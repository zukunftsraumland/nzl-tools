<?php

namespace App\Controller;

use App\Entity\State;
use Doctrine\ORM\EntityManagerInterface;
use Nelmio\ApiDocBundle\Annotation\Model;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

#[Route(path: '/api/v1/states', name: 'api_states_')]
class ApiStatesController extends AbstractController
{

    #[Route(path: '', name: 'index', methods: ['GET'])]
    #[OA\Response(
        response: 200,
        description: 'Returns all states',
        content: new OA\JsonContent(
            type: 'array',
            items: new OA\Items(ref: new Model(type: State::class, groups: ['id', 'state']))
        )
    )]
    #[OA\Tag(name: 'States')]
    public function index(EntityManagerInterface $em, NormalizerInterface $normalizer): JsonResponse
    {
        $states = $em->getRepository(State::class)->findBy([
            //'isPublic' => 1
        ], [], 10000);

        $result = $normalizer->normalize($states, null, [
            'groups' => ['id', 'state'],
        ]);

        return $this->json($result);
    }

    #[Route(path: '/{id}', name: 'get', methods: ['GET'])]
    #[OA\Response(
        response: 200,
        description: 'Returns a single state',
        content: new OA\JsonContent(
            ref: new Model(type: State::class, groups: ['id', 'state'])
        )
    )]
    #[OA\Tag(name: 'States')]
    public function find(Request $request, EntityManagerInterface $em, NormalizerInterface $normalizer): JsonResponse
    {
        $state = $em->getRepository(State::class)
            ->find($request->get('id'));

        $result = $normalizer->normalize($state, null, [
            'groups' => ['id', 'state'],
        ]);

        return $this->json($result);
    }

}