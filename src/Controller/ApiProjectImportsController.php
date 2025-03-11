<?php

namespace App\Controller;

use App\Entity\ProjectImport;
use App\Entity\ProjectImportItem;
use App\Entity\LEPeriod;
use App\Service\ProjectImportManager;
use Doctrine\ORM\EntityManagerInterface;
use Nelmio\ApiDocBundle\Annotation\Model;
use Nelmio\ApiDocBundle\Annotation\Security;
use OpenApi\Attributes as OA;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

#[Route(path: '/api/v1/project-imports', name: 'api_project_imports_')]
class ApiProjectImportsController extends AbstractController
{
    #[Route(path: '', name: 'index', methods: ['GET'])]
    #[IsGranted('ROLE_EDITOR')]
    #[OA\Response(
        response: 200,
        description: 'Returns all project imports',
        content: new OA\JsonContent(
            type: 'array',
            items: new OA\Items(ref: new Model(type: ProjectImport::class, groups: ['id', 'project_import']))
        )
    )]
    #[OA\Tag(name: 'ProjectImports')]
    #[Security(name: 'cookieAuth')]
    public function index(EntityManagerInterface $em, NormalizerInterface $normalizer, ProjectImportManager $importService): JsonResponse
    {
        $imports = $importService->getImports();
        
        return new JsonResponse(
            $normalizer->normalize($imports, null, ['groups' => ['id', 'project_import']])
        );
    }

    #[Route(path: '/{id}', name: 'get', methods: ['GET'], requirements: ['id' => '\d+'])]
    #[IsGranted('ROLE_EDITOR')]
    #[OA\Response(
        response: 200,
        description: 'Returns a single project import',
        content: new OA\JsonContent(
            ref: new Model(type: ProjectImport::class, groups: ['id', 'project_import', 'project_import_details'])
        )
    )]
    #[OA\Tag(name: 'ProjectImports')]
    #[Security(name: 'cookieAuth')]
    public function find(int $id, EntityManagerInterface $em, NormalizerInterface $normalizer, ProjectImportManager $importService): JsonResponse
    {
        $import = $importService->getImport($id);
        
        if (!$import) {
            return new JsonResponse(['error' => 'Import not found'], Response::HTTP_NOT_FOUND);
        }
        
        return new JsonResponse(
            $normalizer->normalize($import, null, ['groups' => ['id', 'project_import', 'project_import_details']])
        );
    }

    #[Route(path: '', name: 'create', methods: ['POST'])]
    #[IsGranted('ROLE_EDITOR')]
    #[OA\RequestBody(
        description: 'The file to upload',
        required: true,
        content: new OA\MediaType(
            mediaType: 'multipart/form-data',
            schema: new OA\Schema(
                type: 'object',
                properties: [
                    new OA\Property(
                        property: 'file',
                        type: 'string',
                        format: 'binary',
                        description: 'The Excel file to upload'
                    )
                ]
            )
        )
    )]
    #[OA\Response(
        response: 200,
        description: 'Returns the created project import',
        content: new OA\JsonContent(
            ref: new Model(type: ProjectImport::class, groups: ['id', 'project_import'])
        )
    )]
    #[OA\Tag(name: 'ProjectImports')]
    #[Security(name: 'cookieAuth')]
    public function create(Request $request, EntityManagerInterface $em, NormalizerInterface $normalizer, ProjectImportManager $importService): JsonResponse
    {
        
        $file = $request->files->get('file');
        
        if (!$file) {
            return new JsonResponse(['error' => 'No file uploaded'], Response::HTTP_BAD_REQUEST);
        }
        
        
        // Check file type
        $mimeType = $file->getMimeType();
        $validMimeTypes = [
            'application/vnd.ms-excel',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'application/octet-stream'
        ];
        
        if (!in_array($mimeType, $validMimeTypes)) {

            return new JsonResponse(['error' => 'Invalid file type. Only Excel files are allowed.'], Response::HTTP_BAD_REQUEST);
        }
        
        try {
            // Create the import
            $user = $this->getUser();

            
            $import = $importService->createImport($file, $user);
            
            return new JsonResponse($normalizer->normalize($import, null, ['groups' => ['id', 'project_import']]));
        } catch (\Exception $e) {
            
            return new JsonResponse([
                'error' => 'An error occurred while processing the import',
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    #[Route(path: '/{id}', name: 'delete', methods: ['DELETE'], requirements: ['id' => '\d+'])]
    #[IsGranted('ROLE_EDITOR')]
    #[OA\Response(
        response: 200,
        description: 'Deletes a project import',
        content: new OA\JsonContent(
            type: 'object',
            properties: [
                new OA\Property(property: 'success', type: 'boolean')
            ]
        )
    )]
    #[OA\Tag(name: 'ProjectImports')]
    #[Security(name: 'cookieAuth')]
    public function delete(int $id, EntityManagerInterface $em, NormalizerInterface $normalizer, ProjectImportManager $importService): JsonResponse
    {
        $import = $importService->getImport($id);
        
        if (!$import) {
            return new JsonResponse(['error' => 'Import not found'], Response::HTTP_NOT_FOUND);
        }
        
        try {
            $importService->deleteImport($import);
            
            return new JsonResponse(['success' => true]);
        } catch (\Exception $e) {
            return new JsonResponse(['error' => $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    #[Route(path: '/{id}/process', name: 'process', methods: ['POST'], requirements: ['id' => '\d+'])]
    #[IsGranted('ROLE_EDITOR')]
    #[OA\RequestBody(
        description: 'Optional parameters for the import process',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(
                    property: 'lePeriodId',
                    type: 'integer',
                    nullable: true,
                    description: 'ID of the LE Period to assign to all imported projects'
                )
            ]
        )
    )]
    #[OA\Response(
        response: 200,
        description: 'Processes the import and creates projects',
        content: new OA\JsonContent(
            ref: new Model(type: ProjectImport::class, groups: ['id', 'project_import'])
        )
    )]
    #[OA\Tag(name: 'ProjectImports')]
    #[Security(name: 'cookieAuth')]
    public function process(Request $request, int $id, EntityManagerInterface $em, NormalizerInterface $normalizer, ProjectImportManager $importService): JsonResponse
    {
        $import = $importService->getImport($id);
        
        if (!$import) {
            return new JsonResponse(['error' => 'Import not found'], Response::HTTP_NOT_FOUND);
        }
        
        // Get the LE Period ID from the request
        $data = json_decode($request->getContent(), true) ?: [];
        $lePeriodId = $data['lePeriodId'] ?? null;
        
        // If a LE Period ID is provided, fetch the LE Period entity
        $lePeriod = null;
        if ($lePeriodId) {
            $lePeriod = $em->getRepository(LEPeriod::class)->find($lePeriodId);
            if (!$lePeriod) {
                return new JsonResponse(['error' => 'LE Period not found'], Response::HTTP_BAD_REQUEST);
            }
        }
        
        // Process the import and create projects
        $result = $importService->importProjects($import, $this->getUser(), $lePeriod);
        
        if (!$result) {
            return new JsonResponse([
                'error' => 'Failed to process import',
                'message' => $import->getErrorMessage()
            ], Response::HTTP_BAD_REQUEST);
        }
        
        return new JsonResponse($normalizer->normalize($import, null, ['groups' => ['id', 'project_import']]));
    }

    #[Route(path: '/{id}/preview', name: 'preview', methods: ['GET'], requirements: ['id' => '\d+'])]
    #[IsGranted('ROLE_EDITOR')]
    #[OA\Response(
        response: 200,
        description: 'Returns a preview of the data to be imported',
        content: new OA\JsonContent(
            type: 'array',
            items: new OA\Items(
                type: 'object',
                properties: [
                    new OA\Property(property: 'rowNumber', type: 'integer'),
                    new OA\Property(property: 'title', type: 'string'),
                    new OA\Property(property: 'description', type: 'string'),
                    new OA\Property(property: 'projectCode', type: 'string'),
                    new OA\Property(property: 'startDate', type: 'string', format: 'date-time'),
                    new OA\Property(property: 'endDate', type: 'string', format: 'date-time'),
                    new OA\Property(property: 'status', type: 'string', enum: ['valid', 'warning', 'error']),
                    new OA\Property(property: 'message', type: 'string'),
                    new OA\Property(property: 'payload', type: 'object', description: 'The full project payload data')
                ]
            )
        )
    )]
    #[OA\Tag(name: 'ProjectImports')]
    #[Security(name: 'cookieAuth')]
    public function preview(int $id, ProjectImportManager $importService): JsonResponse
    {
        $import = $importService->getImport($id);
        
        if (!$import) {
            return new JsonResponse(['error' => 'Import not found'], Response::HTTP_NOT_FOUND);
        }
        
        try {
            // Get preview data from the import service
            $previewData = $importService->generatePreview($import);
            
            return new JsonResponse($previewData);
        } catch (\Exception $e) {

            return new JsonResponse([
                'error' => 'Failed to generate preview',
                'message' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    #[Route(path: '/{importId}/items/{itemId}/process', name: 'process_item', methods: ['POST'], requirements: ['importId' => '\d+', 'itemId' => '\d+'])]
    #[IsGranted('ROLE_EDITOR')]
    #[OA\Response(
        response: 200,
        description: 'Processes a single item in a project import',
        content: new OA\JsonContent(
            ref: new Model(type: ProjectImportItem::class, groups: ['id', 'project_import_item'])
        )
    )]
    #[OA\Tag(name: 'ProjectImports')]
    #[Security(name: 'cookieAuth')]
    public function processItem(int $importId, int $itemId, EntityManagerInterface $em, NormalizerInterface $normalizer, ProjectImportManager $importService): JsonResponse
    {
        $import = $importService->getImport($importId);
        
        if (!$import) {
            return new JsonResponse(['error' => 'Import not found'], Response::HTTP_NOT_FOUND);
        }
        
        $item = $em->getRepository(ProjectImportItem::class)->findOneBy([
            'id' => $itemId,
            'import' => $import
        ]);
        
        if (!$item) {
            return new JsonResponse(['error' => 'Import item not found'], Response::HTTP_NOT_FOUND);
        }
        
        // Process the import item
        $result = $importService->processImportItem($item->getImport(), $item->getRowNumber());
        
        if ($result['status'] === 'error') {
            return new JsonResponse([
                'error' => 'Failed to process import item',
                'message' => $result['message']
            ], Response::HTTP_BAD_REQUEST);
        }
        
        return new JsonResponse($normalizer->normalize($item, null, ['groups' => ['id', 'project_import_item']]));
    }

    #[Route(path: '/importers', name: 'importers_index', methods: ['GET'])]
    #[IsGranted('ROLE_EDITOR')]
    #[OA\Response(
        response: 200,
        description: 'Returns all available project importers',
        content: new OA\JsonContent(
            type: 'array',
            items: new OA\Items(
                type: 'object',
                properties: [
                    new OA\Property(property: 'type', type: 'string'),
                    new OA\Property(property: 'name', type: 'string'),
                    new OA\Property(property: 'description', type: 'string')
                ]
            )
        )
    )]
    #[OA\Tag(name: 'ProjectImports')]
    #[Security(name: 'cookieAuth')]
    public function getImporters(ProjectImportManager $importManager): JsonResponse
    {
        $importers = $importManager->getImporters();
        return $this->json($importers);
    }
} 