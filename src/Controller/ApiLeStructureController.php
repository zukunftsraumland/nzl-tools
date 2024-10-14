<?php

namespace App\Controller;

use App\Entity\LEPeriod;
use App\Entity\LEFundingCategory;
use App\Entity\LEFundingArticle;
use App\Entity\LEFundingMethod;
use Doctrine\ORM\EntityManagerInterface;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;

#[Route(path: '/api/v1/le-structure', name: 'api_le_structure_')]
class ApiLeStructureController extends AbstractController
{
    #[Route(path: '', name: 'index', methods: ['GET'])]
    #[OA\Response(
        response: 200,
        description: 'Returns a list of LE periods with their categories, articles, and methods.',
        content: new OA\JsonContent(
            type: 'array',
            items: new OA\Items(
                type: 'object',
                properties: [
                    new OA\Property(property: 'id', type: 'integer', example: 1),
                    new OA\Property(property: 'name', type: 'string', example: 'Period Name'),
                    new OA\Property(
                        property: 'categories',
                        type: 'array',
                        items: new OA\Items(
                            type: 'object',
                            properties: [
                                new OA\Property(property: 'id', type: 'integer', example: 1),
                                new OA\Property(property: 'name', type: 'string', example: 'Category Name'),
                                new OA\Property(
                                    property: 'articles',
                                    type: 'array',
                                    items: new OA\Items(
                                        type: 'object',
                                        properties: [
                                            new OA\Property(property: 'id', type: 'integer', example: 1),
                                            new OA\Property(property: 'name', type: 'string', example: 'Article Name'),
                                            new OA\Property(
                                                property: 'methods',
                                                type: 'array',
                                                items: new OA\Items(
                                                    type: 'object',
                                                    properties: [
                                                        new OA\Property(property: 'id', type: 'integer', example: 1),
                                                        new OA\Property(property: 'name', type: 'string', example: 'Method Name'),
                                                    ]
                                                )
                                            )
                                        ]
                                    )
                                )
                            ]
                        )
                    )
                ]
            )
        )
    )]
    #[OA\Tag(name: 'LE Structure')]
    public function getLeStructure(EntityManagerInterface $em): JsonResponse
    {
        // Fetch LE periods and related data
        $lePeriods = $em->getRepository(LEPeriod::class)->findAll();
        
        $data = [];
        foreach ($lePeriods as $lePeriod) {
            $categories = [];
            foreach ($lePeriod->getCategories() as $category) {
                $articles = [];
                foreach ($category->getArticles() as $article) {
                    $methods = [];
                    foreach ($article->getMethods() as $method) {
                        $methods[] = ['id' => $method->getId(), 'name' => $method->getName()];
                    }
                    $articles[] = ['id' => $article->getId(), 'name' => $article->getName(), 'methods' => $methods];
                }
                $categories[] = ['id' => $category->getId(), 'name' => $category->getName(), 'articles' => $articles];
            }
            $data[] = ['id' => $lePeriod->getId(), 'name' => $lePeriod->getName(), 'categories' => $categories];
        }

        return $this->json($data);
    }

    // Get a single LEPeriod by ID
    #[Route(path: '/{id}', name: 'get', methods: ['GET'])]
    #[OA\Response(
        response: 200,
        description: 'Returns a single LE period with its categories, articles, and methods.',
        content: new OA\JsonContent(
            type: 'object',
            properties: [
                new OA\Property(property: 'id', type: 'integer', example: 1),
                new OA\Property(property: 'name', type: 'string', example: 'Period Name'),
                new OA\Property(
                    property: 'categories',
                    type: 'array',
                    items: new OA\Items(
                        type: 'object',
                        properties: [
                            new OA\Property(property: 'id', type: 'integer', example: 1),
                            new OA\Property(property: 'name', type: 'string', example: 'Category Name'),
                            new OA\Property(
                                property: 'articles',
                                type: 'array',
                                items: new OA\Items(
                                    type: 'object',
                                    properties: [
                                        new OA\Property(property: 'id', type: 'integer', example: 1),
                                        new OA\Property(property: 'name', type: 'string', example: 'Article Name'),
                                        new OA\Property(
                                            property: 'methods',
                                            type: 'array',
                                            items: new OA\Items(
                                                type: 'object',
                                                properties: [
                                                    new OA\Property(property: 'id', type: 'integer', example: 1),
                                                    new OA\Property(property: 'name', type: 'string', example: 'Method Name'),
                                                ]
                                            )
                                        )
                                    ]
                                )
                            )
                        ]
                    )
                )
            ]
        )
    )]
    #[OA\Tag(name: 'LE Structure')]
    public function find(EntityManagerInterface $em, int $id): JsonResponse
    {
        $lePeriod = $em->getRepository(LEPeriod::class)->find($id);

        $data = [];
        if ($lePeriod) {
            $categories = [];
            foreach ($lePeriod->getCategories() as $category) {
                $articles = [];
                foreach ($category->getArticles() as $article) {
                    $methods = [];
                    foreach ($article->getMethods() as $method) {
                        $methods[] = ['id' => $method->getId(), 'name' => $method->getName()];
                    }
                    $articles[] = ['id' => $article->getId(), 'name' => $article->getName(), 'methods' => $methods];
                }
                $categories[] = ['id' => $category->getId(), 'name' => $category->getName(), 'articles' => $articles];
            }
            $data = ['id' => $lePeriod->getId(), 'name' => $lePeriod->getName(), 'categories' => $categories];
        }

        return $this->json($data);
    }

    // Route to get a LEFundingCategory by ID
    #[Route(path: '/category/{id}', name: 'get_category', methods: ['GET'])]
    #[OA\Response(
        response: 200,
        description: 'Returns a single category with its articles and methods.',
        content: new OA\JsonContent(
            type: 'object',
            properties: [
                new OA\Property(property: 'id', type: 'integer', example: 1),
                new OA\Property(property: 'name', type: 'string', example: 'LEFundingCategory Name'),
                new OA\Property(
                    property: 'articles',
                    type: 'array',
                    items: new OA\Items(
                        type: 'object',
                        properties: [
                            new OA\Property(property: 'id', type: 'integer', example: 1),
                            new OA\Property(property: 'name', type: 'string', example: 'LEFundingArticle Name'),
                            new OA\Property(
                                property: 'methods',
                                type: 'array',
                                items: new OA\Items(
                                    type: 'object',
                                    properties: [
                                        new OA\Property(property: 'id', type: 'integer', example: 1),
                                        new OA\Property(property: 'name', type: 'string', example: 'LEFundingMethod Name'),
                                    ]
                                )
                            )
                        ]
                    )
                )
            ]
        )
    )]
    #[OA\Tag(name: 'LEFundingCategory')]
    public function getCategory(EntityManagerInterface $em, int $id): JsonResponse
    {
        $category = $em->getRepository(LEFundingCategory::class)->find($id);

        $data = [];
        if ($category) {
            $articles = [];
            foreach ($category->getArticles() as $article) {
                $methods = [];
                foreach ($article->getMethods() as $method) {
                    $methods[] = ['id' => $method->getId(), 'name' => $method->getName()];
                }
                $articles[] = ['id' => $article->getId(), 'name' => $article->getName(), 'methods' => $methods];
            }
            $data = ['id' => $category->getId(), 'name' => $category->getName(), 'articles' => $articles];
        }

        return $this->json($data);
    }

    // Route to get a LEFundingArticle by ID
    #[Route(path: '/article/{id}', name: 'get_article', methods: ['GET'])]
    #[OA\Response(
        response: 200,
        description: 'Returns a single article with its methods.',
        content: new OA\JsonContent(
            type: 'object',
            properties: [
                new OA\Property(property: 'id', type: 'integer', example: 1),
                new OA\Property(property: 'name', type: 'string', example: 'LEFundingArticle Name'),
                new OA\Property(
                    property: 'methods',
                    type: 'array',
                    items: new OA\Items(
                        type: 'object',
                        properties: [
                            new OA\Property(property: 'id', type: 'integer', example: 1),
                            new OA\Property(property: 'name', type: 'string', example: 'LEFundingMethod Name'),
                        ]
                    )
                )
            ]
        )
    )]
    #[OA\Tag(name: 'LEFundingArticle')]
    public function getArticle(EntityManagerInterface $em, int $id): JsonResponse
    {
        $article = $em->getRepository(LEFundingArticle::class)->find($id);

        $data = [];
        if ($article) {
            $methods = [];
            foreach ($article->getMethods() as $method) {
                $methods[] = ['id' => $method->getId(), 'name' => $method->getName()];
            }
            $data = ['id' => $article->getId(), 'name' => $article->getName(), 'methods' => $methods];
        }

        return $this->json($data);
    }

    // Route to get a LEFundingMethod by ID
    #[Route(path: '/method/{id}', name: 'get_method', methods: ['GET'])]
    #[OA\Response(
        response: 200,
        description: 'Returns a single method.',
        content: new OA\JsonContent(
            type: 'object',
            properties: [
                new OA\Property(property: 'id', type: 'integer', example: 1),
                new OA\Property(property: 'name', type: 'string', example: 'LEFundingMethod Name')
            ]
        )
    )]
    #[OA\Tag(name: 'LEFundingMethod')]
    public function getMethod(EntityManagerInterface $em, int $id): JsonResponse
    {
        $method = $em->getRepository(LEFundingMethod::class)->find($id);

        $data = [];
        if ($method) {
            $data = ['id' => $method->getId(), 'name' => $method->getName()];
        }

        return $this->json($data);
    }
}

