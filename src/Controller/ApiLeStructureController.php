<?php

namespace App\Controller;

use App\Entity\LEPeriod;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

class ApiLeStructureController extends AbstractController
{
    #[Route('/api/le-structure', name: 'api_le_structure', methods: ['GET'])]
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
}
