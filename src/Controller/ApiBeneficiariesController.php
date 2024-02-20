<?php

namespace App\Controller;

use App\Entity\Beneficiary;
use Doctrine\ORM\EntityManagerInterface;
use Nelmio\ApiDocBundle\Annotation\Model;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

#[Route(path: '/api/v1/beneficiaries', name: 'api_beneficiaries_')]
class ApiBeneficiariesController extends AbstractController
{

    #[Route(path: '', name: 'index', methods: ['GET'])]
    #[OA\Response(
        response: 200,
        description: 'Returns all beneficiaries',
        content: new OA\JsonContent(
            type: 'array',
            items: new OA\Items(ref: new Model(type: Beneficiary::class, groups: ['id', 'beneficiary']))
        )
    )]
    #[OA\Tag(name: 'Beneficiaries')]
    public function index(EntityManagerInterface $em, NormalizerInterface $normalizer): JsonResponse
    {
        $beneficiaries = $em->getRepository(Beneficiary::class)->findBy([
            //'isPublic' => 1
        ], [], 10000);

        $result = $normalizer->normalize($beneficiaries, null, [
            'groups' => ['id', 'beneficiary'],
        ]);

        return $this->json($result);
    }

    #[Route(path: '/{id}', name: 'get', methods: ['GET'])]
    #[OA\Response(
        response: 200,
        description: 'Returns a single beneficiary',
        content: new OA\JsonContent(
            ref: new Model(type: Beneficiary::class, groups: ['id', 'beneficiary'])
        )
    )]
    #[OA\Tag(name: 'Beneficiaries')]
    public function find(Request $request, EntityManagerInterface $em, NormalizerInterface $normalizer): JsonResponse
    {
        $beneficiary = $em->getRepository(Beneficiary::class)
            ->find($request->get('id'));

        $result = $normalizer->normalize($beneficiary, null, [
            'groups' => ['id', 'beneficiary'],
        ]);

        return $this->json($result);
    }

}