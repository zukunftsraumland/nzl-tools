<?php

namespace App\Controller;

use App\Entity\Tag;
use Doctrine\ORM\EntityManagerInterface;
use Nelmio\ApiDocBundle\Annotation\Model;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

#[Route(path: '/api/v1/tags', name: 'api_tags_')]
class ApiTagsController extends AbstractController
{

    #[Route(path: '', name: 'index', methods: ['GET'])]
    #[OA\Response(
        response: 200,
        description: 'Returns all tags',
        content: new OA\JsonContent(
            type: 'array',
            items: new OA\Items(ref: new Model(type: Tag::class, groups: ['id', 'tag']))
        )
    )]
    #[OA\Tag(name: 'Tags')]
    public function index(EntityManagerInterface $em, NormalizerInterface $normalizer): JsonResponse
    {
        $tags = $em->getRepository(Tag::class)->findBy([
            //'isPublic' => 1
        ], [], 10000);

        $result = $normalizer->normalize($tags, null, [
            'groups' => ['id', 'tag'],
        ]);

        return $this->json($result);
    }

    #[Route(path: '/{id}', name: 'get', methods: ['GET'])]
    #[OA\Response(
        response: 200,
        description: 'Returns a single tag',
        content: new OA\JsonContent(
            ref: new Model(type: Tag::class, groups: ['id', 'tag'])
        )
    )]
    #[OA\Tag(name: 'Tags')]
    public function find(Request $request, EntityManagerInterface $em, NormalizerInterface $normalizer): JsonResponse
    {
        $tag = $em->getRepository(Tag::class)
            ->find($request->get('id'));

        $result = $normalizer->normalize($tag, null, [
            'groups' => ['id', 'tag'],
        ]);

        return $this->json($result);
    }


    #[Route(path: '/create', name: 'create', methods: ['POST'])]
    public function create(Request $request, EntityManagerInterface $em, NormalizerInterface $normalizer): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $tag = new Tag();
        $tag->setName($data['name']);
        $tag->setIsPublic(true);
        $tag->setCreatedAt(new \DateTime());
        $tag->setUpdatedAt(new \DateTime());
        // $tag->setContext($data['context'] ? $data['context'] : 'project');
        $em->persist($tag);
        $em->flush();
    
        $result = $normalizer->normalize($tag, null, ['groups' => ['id', 'tag']]);
    
        return $this->json($result);
    }
    

}