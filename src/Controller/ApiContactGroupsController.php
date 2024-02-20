<?php

namespace App\Controller;

use App\Entity\ContactGroup;
use Doctrine\ORM\EntityManagerInterface;
use Nelmio\ApiDocBundle\Annotation\Model;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

#[Route(path: '/api/v1/contact-groups', name: 'api_contact_groups')]
class ApiContactGroupsController extends AbstractController
{

    #[Route(path: '', name: 'index', methods: ['GET'])]
    #[OA\Response(
        response: 200,
        description: 'Returns all contact groups',
        content: new OA\JsonContent(
            type: 'array',
            items: new OA\Items(ref: new Model(type: ContactGroup::class, groups: ['id', 'contact_group']))
        )
    )]
    #[OA\Tag(name: 'ContactGroups')]
    public function index(EntityManagerInterface $em, NormalizerInterface $normalizer): JsonResponse
    {
        $contactGroups = $em->getRepository(ContactGroup::class)->findBy([
            //'isPublic' => 1
        ], [], 10000);

        $result = $normalizer->normalize($contactGroups, null, [
            'groups' => ['id', 'contact_group'],
        ]);

        return $this->json($result);
    }

    #[Route(path: '/{id}', name: 'get', methods: ['GET'])]
    #[OA\Response(
        response: 200,
        description: 'Returns a single contact group',
        content: new OA\JsonContent(
            ref: new Model(type: ContactGroup::class, groups: ['id', 'contact_group'])
        )
    )]
    #[OA\Tag(name: 'ContactGroups')]
    public function find(Request $request, EntityManagerInterface $em, NormalizerInterface $normalizer): JsonResponse
    {
        $contactGroup = $em->getRepository(ContactGroup::class)
            ->find($request->get('id'));

        $result = $normalizer->normalize($contactGroup, null, [
            'groups' => ['id', 'contact_group'],
        ]);

        return $this->json($result);
    }

}