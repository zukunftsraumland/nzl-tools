<?php

namespace App\Controller;

use App\Entity\Topic;
use Doctrine\ORM\EntityManagerInterface;
use Nelmio\ApiDocBundle\Annotation\Model;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

#[Route(path: '/api/v1/topics', name: 'api_topics_')]
class ApiTopicsController extends AbstractController
{

    #[Route(path: '', name: 'index', methods: ['GET'])]
    #[OA\Response(
        response: 200,
        description: 'Returns all topics',
        content: new OA\JsonContent(
            type: 'array',
            items: new OA\Items(ref: new Model(type: Topic::class, groups: ['id', 'topic']))
        )
    )]
    #[OA\Tag(name: 'Topics')]
    public function index(EntityManagerInterface $em, NormalizerInterface $normalizer): JsonResponse
    {
        $topics = $em->getRepository(Topic::class)->findBy([
            //'isPublic' => 1
        ], [], 10000);

        $result = $normalizer->normalize($topics, null, [
            'groups' => ['id', 'topic'],
        ]);

        return $this->json($result);
    }

    #[Route(path: '/{id}', name: 'get', methods: ['GET'])]
    #[OA\Response(
        response: 200,
        description: 'Returns a single topic',
        content: new OA\JsonContent(
            ref: new Model(type: Topic::class, groups: ['id', 'topic'])
        )
    )]
    #[OA\Tag(name: 'Topics')]
    public function find(Request $request, EntityManagerInterface $em, NormalizerInterface $normalizer): JsonResponse
    {
        $topic = $em->getRepository(Topic::class)
            ->find($request->get('id'));

        $result = $normalizer->normalize($topic, null, [
            'groups' => ['id', 'topic'],
        ]);

        return $this->json($result);
    }

}