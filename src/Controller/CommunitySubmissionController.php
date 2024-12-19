<?php

namespace App\Controller;

use App\Service\CommunitySubmissionService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use OpenApi\Attributes as OA;

class CommunitySubmissionController extends AbstractController
{
    public function __construct(
        private CommunitySubmissionService $submissionService
    ) {}

    #[Route('api/v1/community/verify/{token}', name: 'verify_community_submission', methods: ['GET'])]
    #[OA\Tag(name: 'Community Generated Content')]
    #[OA\Get(
        description: "Verifies a user's email address for community-generated content (e.g., project contact messages). When accessed, this endpoint:\n\n" .
            "1. Validates the verification token\n" .
            "2. If valid, marks the submission as verified\n" .
            "3. Processes the verified submission (sends the contact email to project owner)\n" .
            "4. Returns a success/error page to the user",
        parameters: [
            new OA\Parameter(
                name: 'token',
                description: 'One-time verification token sent to user\'s email',
                in: 'path',
                required: true,
                schema: new OA\Schema(type: 'string')
            )
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Returns HTML page showing verification result. Success: Email sent to project owner. Error: Invalid/expired token.'
            )
        ]
    )]
    public function verifySubmission(string $token): Response
    {
        $success = $this->submissionService->verifySubmission($token);

        return $this->render('community/verification.html.twig', [
            'success' => $success
        ]);
    }

    #[Route('api/v1/community/submit/confirmation', name: 'community_submission_confirmation', methods: ['GET'])]
    #[OA\Tag(name: 'Community Generated Content')]
    #[OA\Get(
        description: "Shows confirmation page after submitting community content. Informs user to:\n\n" .
            "1. Check their email for verification link\n" .
            "2. Click the verification link to complete the process\n" .
            "3. Check spam folder if email not received",
        responses: [
            new OA\Response(
                response: 200,
                description: 'Returns HTML page with instructions for email verification'
            )
        ]
    )]
    public function confirmationPage(): Response
    {
        return $this->render('community/confirmation.html.twig');
    }
} 