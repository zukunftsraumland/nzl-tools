<?php

namespace App\Controller;

use Doctrine\ORM\EntityManagerInterface;
use Nelmio\ApiDocBundle\Annotation\Model;
use OpenApi\Attributes as OA;
use App\Entity\Country;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

#[Route(path: '/api/v1/countries', name: 'api_countries_')]
class ApiCountriesController extends AbstractController
{

    #[Route(path: '', name: 'index', methods: ['GET'])]
    #[OA\Response(
        response: 200,
        description: 'Returns all countries',
        content: new OA\JsonContent(
            type: 'array',
            items: new OA\Items(ref: new Model(type: Country::class, groups: ['id', 'country']))
        )
    )]
    #[OA\Tag(name: 'Countries')]
    public function index(EntityManagerInterface $em, NormalizerInterface $normalizer): JsonResponse
    {
        $countries = $em->getRepository(Country::class)->findBy([
            //'isPublic' => 1
        ], [], 10000);

        $result = $normalizer->normalize($countries, null, [
            'groups' => ['id', 'country'],
        ]);

        return $this->json($result);
    }

    #[Route(path: '/{id}', name: 'get', methods: ['GET'])]
    #[OA\Response(
        response: 200,
        description: 'Returns a single country',
        content: new OA\JsonContent(
            ref: new Model(type: Country::class, groups: ['id', 'country'])
        )
    )]
    #[OA\Tag(name: 'Countries')]
    public function find(Request $request, EntityManagerInterface $em, NormalizerInterface $normalizer): JsonResponse
    {
        $country = $em->getRepository(Country::class)
            ->find($request->get('id'));

        $result = $normalizer->normalize($country, null, [
            'groups' => ['id', 'country'],
        ]);

        return $this->json($result);
    }

}