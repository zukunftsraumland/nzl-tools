<?php

namespace App\Controller;

use App\Service\PostService;
use Doctrine\ORM\EntityManagerInterface;
use Nelmio\ApiDocBundle\Annotation\Model;
use Nelmio\ApiDocBundle\Annotation\Security;
use OpenApi\Attributes as OA;
use App\Entity\Post;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

#[Route(path: '/api/v1/posts', name: 'api_posts_')]
class ApiPostsController extends AbstractController
{
    
    #[Route(path: '', name: 'index', methods: ['GET'])]
    #[OA\Parameter(
        name: 'term',
        description: 'Search term',
        in: 'query',
        required: false,
        schema: new OA\Schema(type: 'string'),
    )]
    #[OA\Parameter(
        name: 'topic[]',
        description: 'Include only specific topics (both name or id are valid values)',
        in: 'query',
        required: false,
        schema: new OA\Schema(type: 'array', items: new OA\Items(type: 'string')),
    )]
    #[OA\Parameter(
        name: 'limit',
        description: 'Limit returned items',
        in: 'query',
        required: false,
        schema: new OA\Schema(type: 'integer'),
    )]
    #[OA\Parameter(
        name: 'offset',
        description: 'Skip returned items',
        in: 'query',
        required: false,
        schema: new OA\Schema(type: 'integer'),
    )]
    #[OA\Parameter(
        name: 'orderBy[]',
        description: 'Order items by field',
        in: 'query',
        required: false,
        schema: new OA\Schema(type: 'string', enum: ['id', 'createdAt', 'updatedAt']),
    )]
    #[OA\Parameter(
        name: 'orderDirection[]',
        description: 'Set order direction',
        in: 'query',
        required: false,
        schema: new OA\Schema(type: 'string', enum: ['ASC', 'DESC']),
    )]
    #[OA\Response(
        response: 200,
        description: 'Returns all posts',
        content: new OA\JsonContent(
            type: 'array',
            items: new OA\Items(ref: new Model(type: Post::class, groups: ['id', 'post']))
        )
    )]
    #[OA\Tag(name: 'Posts')]
    public function index(Request $request, EntityManagerInterface $em,
                          NormalizerInterface $normalizer): JsonResponse
    {
        $qb = $em->createQueryBuilder();

        $qb
            ->select('e')
            ->from(Post::class, 'e')
        ;

        if($request->get('term')) {
            $qb
                ->andWhere('(e.title LIKE :term OR e.description LIKE :term OR e.text LIKE :term OR e.translations LIKE :term)')
                ->setParameter('term', '%'.$request->get('term').'%');
        }

        if($request->get('topic') && is_array($request->get('topic')) && count($request->get('topic'))) {
            foreach($request->get('topic') as $key => $topic) {
                $qb
                    ->leftJoin('e.topics', 'topic'.$key)
                    ->andWhere('topic'.$key.'.name = :topic'.$key.' OR topic'.$key.'.id = :topicId'.$key)
                    ->setParameter('topic'.$key, $topic)
                    ->setParameter('topicId'.$key, $topic)
                ;
            }
        }

        if($request->get('limit')) {
            $qb->setMaxResults($request->get('limit'));
        }

        if($request->get('offset')) {
            $qb->setFirstResult($request->get('offset'));
        }

        if($request->get('orderBy') && is_array($request->get('orderBy')) && count($request->get('orderBy'))) {

            foreach($request->get('orderBy') as $key => $orderBy) {

                if(!in_array($orderBy, ['id', 'createdAt', 'updatedAt'])) {
                    continue;
                }

                $direction = 'ASC';

                if($request->get('orderDirection') && is_array($request->get('orderDirection')) &&
                    count($request->get('orderDirection')) && array_key_exists($key, $request->get('orderDirection')) &&
                    in_array($request->get('orderDirection')[$key], ['ASC', 'DESC'])) {
                    $direction = $request->get('orderDirection')[$key];
                }

                $qb
                    ->addOrderBy('e.'.$orderBy, $direction)
                ;

            }

        } else {
            $qb
                ->addOrderBy('e.date', 'DESC')
                ->addOrderBy('e.title', 'ASC')
            ;
        }

        $posts = $qb->getQuery()->getResult();

        $result = $normalizer->normalize($posts, null, [
            'groups' => ['id', 'post', 'topic'],
        ]);

        return $this->json($result);
    }
    
    #[Route(path: '/{id}', name: 'get', methods: ['GET'])]
    #[OA\Response(
        response: 200,
        description: 'Returns a single post',
        content: new OA\JsonContent(
            ref: new Model(type: Post::class, groups: ['id', 'post'])
        )
    )]
    #[OA\Tag(name: 'Posts')]
    public function find(Request $request, EntityManagerInterface $em,
                         NormalizerInterface $normalizer): JsonResponse
    {
        $post = $em->getRepository(Post::class)
            ->find($request->get('id'));

        $result = $normalizer->normalize($post, null, [
            'groups' => ['id', 'post', 'topic'],
        ]);

        return $this->json($result);
    }
    
    #[Route(path: '', name: 'create', methods: ['POST'])]
    #[IsGranted('ROLE_EDITOR')]
    #[OA\Response(
        response: 200,
        description: 'Create a post',
        content: new OA\JsonContent(
            ref: new Model(type: Post::class, groups: ['id', 'post'])
        )
    )]
    #[OA\Tag(name: 'Posts')]
    #[Security(name: 'cookieAuth')]
    public function create(Request $request, EntityManagerInterface $em,
                           NormalizerInterface $normalizer, PostService $postService): JsonResponse
    {
        $payload = json_decode($request->getContent(), true);

        if(($errors = $postService->validatePostPayload($payload)) !== true) {
            return $this->json($errors, 400);
        }

        $post = $postService->createPost($payload);

        $result = $normalizer->normalize($post, null, [
            'groups' => ['id', 'post'],
        ]);

        return $this->json($result);
    }
    
    #[Route(path: '/{id}', name: 'update', methods: ['PUT'])]
    #[IsGranted('ROLE_EDITOR')]
    #[OA\Response(
        response: 200,
        description: 'Update a post',
        content: new OA\JsonContent(
            ref: new Model(type: Post::class, groups: ['id', 'post'])
        )
    )]
    #[OA\Tag(name: 'Posts')]
    #[Security(name: 'cookieAuth')]
    public function update(Request $request, EntityManagerInterface $em,
                           NormalizerInterface $normalizer, PostService $postService): JsonResponse
    {
        $post = $em->getRepository(Post::class)
            ->find($request->get('id'));

        $payload = json_decode($request->getContent(), true);

        if(($errors = $postService->validatePostPayload($payload)) !== true) {
            return $this->json($errors, 400);
        }

        $post = $postService->updatePost($post, $payload);

        $result = $normalizer->normalize($post, null, [
            'groups' => ['id', 'post'],
        ]);

        return $this->json($result);
    }
    
    #[Route(path: '/{id}', name: 'delete', methods: ['DELETE'])]
    #[IsGranted('ROLE_EDITOR')]
    #[OA\Response(
        response: 200,
        description: 'Delete a post',
        content: new OA\JsonContent(
            type: 'array',
            items: new OA\Items(type: 'string'),
            default: []
        )
    )]
    #[OA\Tag(name: 'Posts')]
    #[Security(name: 'cookieAuth')]
    public function delete(Request $request, EntityManagerInterface $em,
                           NormalizerInterface $normalizer, PostService $postService): JsonResponse
    {
        $post = $em->getRepository(Post::class)
            ->find($request->get('id'));

        $postService->deletePost($post);

        return $this->json([]);
    }
    
}