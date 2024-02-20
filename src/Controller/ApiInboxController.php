<?php

namespace App\Controller;

use App\Entity\Inbox;
use Doctrine\ORM\EntityManagerInterface;
use Nelmio\ApiDocBundle\Annotation\Model;
use Nelmio\ApiDocBundle\Annotation\Security;
use OpenApi\Attributes as OA;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

#[Route(path: '/api/v1/inbox', name: 'api_inbox_')]
class ApiInboxController extends AbstractController
{

    #[Route(path: '', name: 'index', methods: ['GET'])]
    #[IsGranted('ROLE_EDITOR')]
    #[OA\Response(
        response: 200,
        description: 'Returns all inbox items',
        content: new OA\JsonContent(
            type: 'array',
            items: new OA\Items(ref: new Model(type: Inbox::class, groups: ['id', 'inbox']))
        )
    )]
    #[OA\Tag(name: 'Inbox')]
    #[Security(name: 'cookieAuth')]
    public function index(EntityManagerInterface $em, NormalizerInterface $normalizer): JsonResponse
    {

        $inbox = $em->getRepository(Inbox::class)->findBy([
            'isMerged' => false,
            'type' => 'topic',
        ], ['createdAt' => 'ASC'], 500);

        $inbox = array_merge($inbox, $em->getRepository(Inbox::class)->findBy([
            'isMerged' => false,
            'type' => 'project',
        ], ['status' => 'ASC', 'createdAt' => 'ASC'], 500));

        $inbox = array_merge($inbox, $em->getRepository(Inbox::class)->findBy([
            'isMerged' => false,
            'type' => 'state',
        ], ['createdAt' => 'ASC'], 500));

        $inbox = array_merge($inbox, $em->getRepository(Inbox::class)->findBy([
            'isMerged' => false,
            'type' => 'country',
        ], ['createdAt' => 'ASC'], 500));

        $inbox = array_merge($inbox, $em->getRepository(Inbox::class)->findBy([
            'isMerged' => false,
            'type' => 'program',
        ], ['createdAt' => 'ASC'], 500));

        $inbox = array_merge($inbox, $em->getRepository(Inbox::class)->findBy([
            'isMerged' => false,
            'type' => 'instrument',
        ], ['createdAt' => 'ASC'], 500));

        $result = $normalizer->normalize($inbox, null, [
            'groups' => ['id', 'inbox'],
        ]);

        return $this->json($result);

    }

    #[Route(path: '/{id}', name: 'get', methods: ['GET'])]
    #[IsGranted('ROLE_EDITOR')]
    #[OA\Response(
        response: 200,
        description: 'Returns a single inbox item',
        content: new OA\JsonContent(
            ref: new Model(type: Inbox::class, groups: ['id', 'inbox'])
        )
    )]
    #[OA\Tag(name: 'Inbox')]
    #[Security(name: 'cookieAuth')]
    public function find(Request $request, EntityManagerInterface $em,
                         NormalizerInterface $normalizer): JsonResponse
    {

        $inboxItem = $em->getRepository(Inbox::class)
            ->find($request->get('id'));

        $result = $normalizer->normalize($inboxItem, null, [
            'groups' => ['id', 'inbox'],
        ]);

        return $this->json($result);
    }

    #[Route(path: '/{id}', name: 'delete', methods: ['DELETE'])]
    #[IsGranted('ROLE_EDITOR')]
    #[OA\Response(
        response: 200,
        description: 'Delete an inbox item',
        content: new OA\JsonContent(
            type: 'array',
            items: new OA\Items(type: 'string'),
            default: []
        )
    )]
    #[OA\Tag(name: 'Inbox')]
    #[Security(name: 'cookieAuth')]
    public function delete(Request $request, EntityManagerInterface $em,
                           NormalizerInterface $normalizer): JsonResponse
    {
        $inboxItem = $em->getRepository(Inbox::class)
            ->find($request->get('id'));
        
        $em->remove($inboxItem);
        $em->flush();

        return $this->json([]);
    }

}