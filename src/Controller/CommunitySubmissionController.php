<?php

namespace App\Controller;

use App\Service\CommunitySubmissionService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class CommunitySubmissionController extends AbstractController
{
    public function __construct(
        private CommunitySubmissionService $submissionService
    ) {}

    #[Route('api/v1/community/verify/{token}', name: 'verify_community_submission')]
    public function verifySubmission(string $token): Response
    {
        $success = $this->submissionService->verifySubmission($token);

        return $this->render('community/verification.html.twig', [
            'success' => $success
        ]);
    }

    #[Route('api/v1/community/submit/confirmation', name: 'community_submission_confirmation')]
    public function confirmationPage(): Response
    {
        return $this->render('community/confirmation.html.twig');
    }
} 