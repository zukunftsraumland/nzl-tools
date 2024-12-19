<?php

namespace App\Service;

use App\Entity\CommunitySubmission;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Twig\Environment;
use App\Entity\Project;
use App\Service\LogService;

class CommunitySubmissionService
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private MailerInterface $mailer,
        private UrlGeneratorInterface $urlGenerator,
        private Environment $twig,
        private LogService $logService,
        private string $mailerFrom
    ) {}

    public function createPendingSubmission(array $submissionData, string $type = CommunitySubmission::TYPE_PROJECT_CONTACT): CommunitySubmission
    {
        // Extract email from contact info
        $email = $submissionData['contactInfo']['email'] ?? null;
        if (!$email) {
            throw new \InvalidArgumentException('Email is required for submission');
        }

        $submission = new CommunitySubmission($type);
        $submission->setSubmissionData($submissionData);
        $submission->setEmail($email);

        $this->entityManager->persist($submission);
        $this->entityManager->flush();

        $this->sendVerificationEmail($submission);

        return $submission;
    }

    public function verifySubmission(string $token): bool
    {
        $submission = $this->entityManager->getRepository(CommunitySubmission::class)
            ->findOneBy(['verificationToken' => $token, 'isVerified' => false]);

        if (!$submission) {
            return false;
        }

        // Mark as verified
        $submission->setIsVerified(true);
        
        // Process the submission based on type
        $this->processVerifiedSubmission($submission);

        $this->entityManager->flush();

        return true;
    }

    private function processVerifiedSubmission(CommunitySubmission $submission): void
    {
        $data = $submission->getSubmissionData();
        
        switch ($submission->getType()) {
            case CommunitySubmission::TYPE_PROJECT_CONTACT:
                // Process project contact email
                if (isset($data['type']) && $data['type'] === 'project_contact') {
                    $this->sendProjectContactEmail($data);
                }
                break;
            
            default:
                throw new \InvalidArgumentException('Unknown submission type: ' . $submission->getType());
        }
    }

    private function sendProjectContactEmail(array $data): void
    {
        // Get project
        $project = $this->entityManager->getRepository(Project::class)->find($data['projectId']);
        if (!$project) {
            throw new \Exception('Project not found');
        }

        // Get contact email from project
        $contactEmail = null;
        $contacts = $project->getContacts();
        if (!empty($contacts) && isset($contacts[0]['email']) && !empty($contacts[0]['email'])) {
            $contactEmail = $contacts[0]['email'];
        }

        if (!$contactEmail) {
            $contactEmail = 'info@zukunftsraumland.at';
        }

        try {
            // Create email
            $email = (new Email())
                ->from($this->mailerFrom)
                ->to($contactEmail)
                ->replyTo($data['contactInfo']['email'])
                ->subject('Neue Kontaktanfrage: ' . $data['subject'])
                ->html($this->twig->render('emails/project_contact.html.twig', [
                    'project' => $project,
                    'data' => array_merge($data['contactInfo'], [
                        'subject' => $data['subject'],
                        'message' => $data['message'],
                        'fileName' => isset($data['attachment']) ? $data['attachment']['name'] : null
                    ])
                ]));

            // Add attachment if exists
            if (isset($data['attachment'])) {
                $tmpFile = tempnam(sys_get_temp_dir(), 'attachment_');
                if ($tmpFile === false) {
                    throw new \Exception('Could not create temporary file');
                }

                if (file_put_contents($tmpFile, base64_decode($data['attachment']['data'])) === false) {
                    throw new \Exception('Could not write to temporary file');
                }

                try {
                    $email->attachFromPath($tmpFile, $data['attachment']['name']);
                    // Send email with attachment
                    $this->mailer->send($email);
                } finally {
                    // Clean up temp file after email is sent or if an error occurs
                    if (file_exists($tmpFile)) {
                        unlink($tmpFile);
                    }
                }
            } else {
                // Send email without attachment
                $this->mailer->send($email);
            }

            // Log the successful email
            $this->logService->createLog([
                'context' => 'Project Contact',
                'category' => 'Email',
                'action' => 'sent',
                'value' => json_encode([
                    'projectId' => $project->getId(),
                    'from' => $data['contactInfo']['email'],
                    'to' => $contactEmail,
                    'subject' => $data['subject'],
                    'hasAttachment' => isset($data['attachment'])
                ])
            ]);

        } catch (\Exception $e) {
            $this->logService->createLog([
                'context' => 'Project Contact',
                'category' => 'Email',
                'action' => 'failed',
                'value' => json_encode([
                    'projectId' => $project->getId(),
                    'error' => $e->getMessage()
                ])
            ]);
            throw $e; // Re-throw the exception to be handled by the caller
        }
    }

    private function sendVerificationEmail(CommunitySubmission $submission): void
    {
        $verificationUrl = $this->urlGenerator->generate('verify_community_submission', 
            ['token' => $submission->getVerificationToken()],
            UrlGeneratorInterface::ABSOLUTE_URL
        );

        $subject = match($submission->getType()) {
            CommunitySubmission::TYPE_PROJECT_CONTACT => 'Bestätigen Sie Ihre E-Mail Adresse - zukunftsraumland.at',
            default => 'Bestätigen Sie Ihre Eingabe - zukunftsraumland.at',
        };

        $email = (new Email())
            ->from('noreply@zukunftsraumland.at')
            ->to($submission->getEmail())
            ->subject($subject)
            ->html($this->getEmailTemplate($verificationUrl, $submission->getType()));

        $this->mailer->send($email);
    }

    private function getEmailTemplate(string $verificationUrl, string $type): string
    {
        $title = match($type) {
            CommunitySubmission::TYPE_PROJECT_CONTACT => 'Bestätigen Sie Ihre E-Mail Adresse',
            default => 'Bestätigen Sie Ihre Eingabe',
        };

        $description = match($type) {
            CommunitySubmission::TYPE_PROJECT_CONTACT => 'Vielen Dank für Ihre Kontaktanfrage! Um die Anfrage zu versenden,',
            default => 'Vielen Dank für Ihre Eingabe auf zukunftsraumland.at. Um die Veröffentlichung abzuschliessen,',
        };


        return <<<HTML
            <div style="font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto;">
                <img src="https://www.zukunftsraumland.at/wp-content/themes/theme/assets/img/logo.svg" alt="netzwerkzukunftsraum Land Logo" style="max-width: 200px; margin-bottom: 20px;">
                <h2 style="color: #333;">{$title}</h2>
                <p style="color: #666; line-height: 1.5;">
                    {$description}
                    klicken Sie bitte auf den folgenden Link:
                </p>
                <p style="margin: 30px 0;">
                    <a href="{$verificationUrl}" 
                       style="background: #5077B2; color: white; padding: 12px 25px; text-decoration: none; border-radius: 4px;">
                        E-Mail Adresse bestätigen
                    </a>
                </p>
                <p style="color: #666; line-height: 1.5;">
                    Falls der Button nicht funktioniert, kopieren Sie bitte diesen Link in Ihren Browser:<br>
                    <span style="color: #5077B2;">{$verificationUrl}</span>
                </p>
                <hr style="border: none; border-top: 1px solid #eee; margin: 30px 0;">
                <p style="color: #999; font-size: 0.9em;">
                    Dies ist eine automatisch generierte E-Mail. Bitte antworten Sie nicht auf diese Nachricht.
                </p>
            </div>
        HTML;
    }
} 