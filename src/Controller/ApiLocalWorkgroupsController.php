<?php

namespace App\Controller;

use App\Entity\LocalWorkgroup;
use Doctrine\ORM\EntityManagerInterface;
use Nelmio\ApiDocBundle\Annotation\Model;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

#[Route(path: '/api/v1/local-workgroups', name: 'api_local_workgroups_')]
class ApiLocalWorkgroupsController extends AbstractController
{

    #[Route(path: '', name: 'index', methods: ['GET'])]
    #[OA\Response(
        response: 200,
        description: 'Returns all local workgroups (LAG)',
        content: new OA\JsonContent(
            type: 'array',
            items: new OA\Items(ref: new Model(type: LocalWorkgroup::class, groups: ['id', 'localworkgroup']))
        )
    )]
    #[OA\Tag(name: 'Local Workgroups (LAG)')]
    public function index(EntityManagerInterface $em, NormalizerInterface $normalizer): JsonResponse
    {
        $localworkgroups = $em->getRepository(LocalWorkgroup::class)->findBy([
            //'isPublic' => 1
        ], [], 10000);

        $result = $normalizer->normalize($localworkgroups, null, [
            'groups' => ['id','localworkgroup'],
        ]);

        return $this->json($result);
    }

    #[Route(path: '/{id}', name: 'get', methods: ['GET'])]
    #[OA\Response(
        response: 200,
        description: 'Returns a single local workgroup (LAG)',
        content: new OA\JsonContent(
            ref: new Model(type: LocalWorkgroup::class, groups: ['id', 'localworkgroup'])
        )
    )]
    #[OA\Tag(name: 'Local Workgroup')]
    public function find(Request $request, EntityManagerInterface $em, NormalizerInterface $normalizer): JsonResponse
    {
        $localworkgroup = $em->getRepository(LocalWorkgroup::class)
            ->find($request->get('id'));

        $result = $normalizer->normalize($localworkgroup, null, [
            'groups' => ['id', 'localworkgroup'],
        ]);

        return $this->json($result);
    }

}