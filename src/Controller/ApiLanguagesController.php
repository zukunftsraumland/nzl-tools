<?php

namespace App\Controller;

use App\Entity\Language;
use Doctrine\ORM\EntityManagerInterface;
use Nelmio\ApiDocBundle\Annotation\Model;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

#[Route(path: '/api/v1/languages', name: 'api_languages_')]
class ApiLanguagesController extends AbstractController
{

    #[Route(path: '', name: 'index', methods: ['GET'])]
    #[OA\Response(
        response: 200,
        description: 'Returns all languages',
        content: new OA\JsonContent(
            type: 'array',
            items: new OA\Items(ref: new Model(type: Language::class, groups: ['id', 'language']))
        )
    )]
    #[OA\Tag(name: 'Languages')]
    public function index(EntityManagerInterface $em, NormalizerInterface $normalizer): JsonResponse
    {
        $languages = $em->getRepository(Language::class)->findBy([
            //'isPublic' => 1
        ], [], 10000);

        $result = $normalizer->normalize($languages, null, [
            'groups' => ['id', 'language'],
        ]);

        return $this->json($result);
    }

    #[Route(path: '/{id}', name: 'get', methods: ['GET'])]
    #[OA\Response(
        response: 200,
        description: 'Returns a single language',
        content: new OA\JsonContent(
            ref: new Model(type: Language::class, groups: ['id', 'language'])
        )
    )]
    #[OA\Tag(name: 'Languages')]
    public function find(Request $request, EntityManagerInterface $em, NormalizerInterface $normalizer): JsonResponse
    {
        $language = $em->getRepository(Language::class)
            ->find($request->get('id'));

        $result = $normalizer->normalize($language, null, [
            'groups' => ['id', 'language'],
        ]);

        return $this->json($result);
    }

}