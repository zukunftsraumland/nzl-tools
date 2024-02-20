<?php

namespace App\Controller;

use App\Entity\InteractiveGraphic;
use App\Service\InteractiveGraphicService;
use Doctrine\ORM\EntityManagerInterface;
use Nelmio\ApiDocBundle\Annotation\Model;
use Nelmio\ApiDocBundle\Annotation\Security;
use OpenApi\Attributes as OA;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

#[Route(path: '/api/v1/interactive-graphics', name: 'api_interactive_graphics_')]
class ApiInteractiveGraphicsController extends AbstractController
{
    
    #[Route(path: '', name: 'index', methods: ['GET'])]
    #[OA\Response(
        response: 200,
        description: 'Returns all interactive graphics',
        content: new OA\JsonContent(
            type: 'array',
            items: new OA\Items(ref: new Model(type: InteractiveGraphic::class, groups: ['id', 'interactive_graphic']))
        )
    )]
    #[OA\Tag(name: 'Interactive Graphics')]
    public function index(EntityManagerInterface $em, NormalizerInterface $normalizer): JsonResponse
    {
        $interactiveGraphics = $em->getRepository(InteractiveGraphic::class)->findAll();

        $result = $normalizer->normalize($interactiveGraphics, null, [
            'groups' => ['id', 'interactive_graphic'],
        ]);

        return $this->json($result);
    }
    
    #[Route(path: '/{id}', name: 'get', methods: ['GET'])]
    #[OA\Response(
        response: 200,
        description: 'Returns a single interactive graphic',
        content: new OA\JsonContent(
            ref: new Model(type: InteractiveGraphic::class, groups: ['id', 'interactive_graphic'])
        )
    )]
    #[OA\Tag(name: 'Interactive Graphics')]
    public function find(Request $request, EntityManagerInterface $em, NormalizerInterface $normalizer): JsonResponse
    {
        $interactiveGraphic = $em->getRepository(InteractiveGraphic::class)
            ->find($request->get('id'));

        $result = $normalizer->normalize($interactiveGraphic, null, [
            'groups' => ['id', 'interactive_graphic'],
        ]);

        return $this->json($result);
    }
    
    #[Route(path: '', name: 'create', methods: ['POST'])]
    #[IsGranted('ROLE_EDITOR')]
    #[OA\Response(
        response: 200,
        description: 'Create an interactive graphic',
        content: new OA\JsonContent(
            ref: new Model(type: InteractiveGraphic::class, groups: ['id', 'interactive_graphic'])
        )
    )]
    #[OA\Tag(name: 'Interactive Graphics')]
    #[Security(name: 'cookieAuth')]
    public function create(Request $request, EntityManagerInterface $em, NormalizerInterface $normalizer,
                           InteractiveGraphicService $interactiveGraphicService): JsonResponse
    {
        $payload = json_decode($request->getContent(), true);

        if(($errors = $interactiveGraphicService->validateInteractiveGraphicPayload($payload)) !== true) {
            return $this->json($errors, 400);
        }

        $interactiveGraphic = $interactiveGraphicService->createInteractiveGraphic($payload);

        $result = $normalizer->normalize($interactiveGraphic, null, [
            'groups' => ['id', 'interactive_graphic'],
        ]);

        return $this->json($result);
    }
    
    #[Route(path: '/{id}', name: 'update', methods: ['PUT'])]
    #[IsGranted('ROLE_EDITOR')]
    #[OA\Response(
        response: 200,
        description: 'Update an interactive graphic',
        content: new OA\JsonContent(
            ref: new Model(type: InteractiveGraphic::class, groups: ['id', 'interactive_graphic'])
        )
    )]
    #[OA\Tag(name: 'Interactive Graphics')]
    #[Security(name: 'cookieAuth')]
    public function update(Request $request, EntityManagerInterface $em, NormalizerInterface $normalizer,
                           InteractiveGraphicService $interactiveGraphicService): JsonResponse
    {
        $interactiveGraphic = $em->getRepository(InteractiveGraphic::class)
            ->find($request->get('id'));

        $payload = json_decode($request->getContent(), true);

        if(($errors = $interactiveGraphicService->validateInteractiveGraphicPayload($payload)) !== true) {
            return $this->json($errors, 400);
        }

        $interactiveGraphic = $interactiveGraphicService->updateInteractiveGraphic($interactiveGraphic, $payload);

        $result = $normalizer->normalize($interactiveGraphic, null, [
            'groups' => ['id', 'interactive_graphic'],
        ]);

        return $this->json($result);
    }
    
    #[Route(path: '/{id}', name: 'delete', methods: ['DELETE'])]
    #[IsGranted('ROLE_EDITOR')]
    #[OA\Response(
        response: 200,
        description: 'Delete an interactive graphic',
        content: new OA\JsonContent(
            type: 'array',
            items: new OA\Items(type: 'string'),
            default: []
        )
    )]
    #[OA\Tag(name: 'Interactive Graphics')]
    #[Security(name: 'cookieAuth')]
    public function delete(Request $request, EntityManagerInterface $em, NormalizerInterface $normalizer,
                           InteractiveGraphicService $interactiveGraphicService): JsonResponse
    {
        $interactiveGraphic = $em->getRepository(InteractiveGraphic::class)
            ->find($request->get('id'));

        $interactiveGraphicService->deleteInteractiveGraphic($interactiveGraphic);

        return $this->json([]);
    }
    
    #[Route(path: '/public/{id}', name: 'public', methods: ['GET'])]
    #[OA\Response(
        response: 200,
        description: 'Returns a single interactive graphic',
        content: new OA\JsonContent(
            ref: new Model(type: InteractiveGraphic::class, groups: ['id', 'interactive_graphic'])
        )
    )]
    #[OA\Tag(name: 'Interactive Graphics')]
    public function publicAction(Request $request, EntityManagerInterface $em, NormalizerInterface $normalizer): JsonResponse
    {
        $interactiveGraphic = $em
            ->getRepository(InteractiveGraphic::class)
            ->find($request->get('id'));

        if(!$interactiveGraphic) {
            throw $this->createNotFoundException();
        }

        $result = $normalizer->normalize($interactiveGraphic, null, [
            'groups' => ['id', 'interactive_graphic'],
        ]);

        return $this->json($result);
    }
    
}