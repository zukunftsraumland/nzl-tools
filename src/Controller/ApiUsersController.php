<?php

namespace App\Controller;

use App\Service\UserService;
use Doctrine\ORM\EntityManagerInterface;
use Nelmio\ApiDocBundle\Annotation\Model;
use Nelmio\ApiDocBundle\Annotation\Security;
use OpenApi\Attributes as OA;
use App\Entity\User;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

#[Route(path: '/api/v1/users', name: 'api_users_')]
class ApiUsersController extends AbstractController
{

    #[Route(path: '/me', name: 'me', methods: ['GET'])]
    #[OA\Response(
        response: 200,
        description: 'Returns authenticated user data',
        content: new OA\JsonContent(
            ref: new Model(type: User::class, groups: ['id', 'user'])
        )
    )]
    #[OA\Tag(name: 'Users')]
    public function me(Request $request, EntityManagerInterface $em,
                          NormalizerInterface $normalizer): JsonResponse
    {
        $user = $em->getRepository(User::class)->findOneBy([
            'email' => $this->getUser()->getUserIdentifier(),
        ]);

        if(!$user) {
            return $this->json([]);
        }

        $result = $normalizer->normalize($user, null, [
            'groups' => ['id', 'user'],
        ]);

        $hierarchy = $this->getParameter('security.role_hierarchy.roles');
        $availableRoles = [];
        array_walk_recursive($hierarchy, function($role) use (&$availableRoles) {
            $availableRoles[] = $role;
        });
        $availableRoles = array_unique(array_merge($availableRoles, array_keys($hierarchy)));
        $result['roles'] = array_values(array_filter($availableRoles, function ($role) {
            return $this->isGranted($role);
        }));

        return $this->json($result);
    }

    #[Route(path: '', name: 'index', methods: ['GET'])]
    #[IsGranted('ROLE_EDITOR')]
    #[OA\Response(
        response: 200,
        description: 'Returns all users',
        content: new OA\JsonContent(
            type: 'array',
            items: new OA\Items(ref: new Model(type: User::class, groups: ['id', 'user']))
        )
    )]
    #[OA\Tag(name: 'Users')]
    #[Security(name: 'cookieAuth')]
    public function index(Request $request, EntityManagerInterface $em,
                          NormalizerInterface $normalizer): JsonResponse
    {
        $users = $em->getRepository(User::class)->findBy([
            //'isPublic' => 1
        ], [], 10000);

        $result = $normalizer->normalize($users, null, [
            'groups' => ['id', 'user'],
        ]);

        return $this->json($result);
    }
    
    #[Route(path: '/{id}', name: 'get', methods: ['GET'])]
    #[IsGranted('ROLE_EDITOR')]
    #[OA\Response(
        response: 200,
        description: 'Returns a single user',
        content: new OA\JsonContent(
            ref: new Model(type: User::class, groups: ['id', 'user'])
        )
    )]
    #[OA\Tag(name: 'Users')]
    #[Security(name: 'cookieAuth')]
    public function find(Request $request, EntityManagerInterface $em,
                         NormalizerInterface $normalizer): JsonResponse
    {
        $user = $em->getRepository(User::class)
            ->find($request->get('id'));

        $result = $normalizer->normalize($user, null, [
            'groups' => ['id', 'user'],
        ]);

        return $this->json($result);
    }
    
    #[Route(path: '', name: 'create', methods: ['POST'])]
    #[IsGranted('ROLE_ADMIN')]
    #[OA\Response(
        response: 200,
        description: 'Create an user',
        content: new OA\JsonContent(
            ref: new Model(type: User::class, groups: ['id', 'user'])
        )
    )]
    #[OA\Tag(name: 'Users')]
    #[Security(name: 'cookieAuth')]
    public function create(Request $request, EntityManagerInterface $em,
                           NormalizerInterface $normalizer, UserService $userService): JsonResponse
    {
        $payload = json_decode($request->getContent(), true);

        if(($errors = $userService->validateUserPayload($payload)) !== true) {
            return $this->json($errors, 400);
        }

        $user = $userService->createUser($payload);

        $result = $normalizer->normalize($user, null, [
            'groups' => ['id', 'user'],
        ]);

        return $this->json($result);
    }
    
    #[Route(path: '/{id}', name: 'update', methods: ['PUT'])]
    #[IsGranted('ROLE_ADMIN')]
    #[OA\Response(
        response: 200,
        description: 'Update an user',
        content: new OA\JsonContent(
            ref: new Model(type: User::class, groups: ['id', 'user'])
        )
    )]
    #[OA\Tag(name: 'Users')]
    #[Security(name: 'cookieAuth')]
    public function update(Request $request, EntityManagerInterface $em,
                           NormalizerInterface $normalizer, UserService $userService): JsonResponse
    {
        $user = $em->getRepository(User::class)
            ->find($request->get('id'));

        $payload = json_decode($request->getContent(), true);

        if(($errors = $userService->validateUserPayload($payload)) !== true) {
            return $this->json($errors, 400);
        }

        $user = $userService->updateUser($user, $payload);

        $result = $normalizer->normalize($user, null, [
            'groups' => ['id', 'user'],
        ]);

        return $this->json($result);
    }
    
    #[Route(path: '/{id}', name: 'delete', methods: ['DELETE'])]
    #[IsGranted('ROLE_ADMIN')]
    #[OA\Response(
        response: 200,
        description: 'Delete an user',
        content: new OA\JsonContent(
            type: 'array',
            items: new OA\Items(type: 'string'),
            default: []
        )
    )]
    #[OA\Tag(name: 'Users')]
    #[Security(name: 'cookieAuth')]
    public function delete(Request $request, EntityManagerInterface $em,
                           NormalizerInterface $normalizer, UserService $userService): JsonResponse
    {
        $user = $em->getRepository(User::class)
            ->find($request->get('id'));

        $userService->deleteUser($user);

        return $this->json([]);
    }

}