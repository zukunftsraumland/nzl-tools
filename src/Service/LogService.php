<?php

namespace App\Service;

use App\Entity\Log;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Core\Security;

class LogService {

    protected $em;
    protected $requestStack;
    protected $security;

    public function __construct(EntityManagerInterface $em, RequestStack $requestStack, Security $security)
    {
        $this->em = $em;
        $this->requestStack = $requestStack;
        $this->security = $security;
    }

    public function validateFields($payload, $fields = [])
    {
        foreach($fields as $field) {
            if(!array_key_exists($field, $payload)) {
                return [
                    [
                        'field' => $field,
                    ]
                ];
            }
        }

        return true;
    }

    public function validateLogPayload($payload)
    {
        if(($errors = $this->validateFields($payload, [
            'context',
            'category',
            'action',
            'value',
        ])) !== true) {
            return $errors;
        }

        return true;
    }

    public function createLog($payload)
    {
        $log = new Log();

        $log->setCreatedAt(new \DateTime());

        $log = $this->applyLogPayload($payload, $log);

        $this->em->persist($log);
        $this->em->flush();

        return $log;
    }

    public function updateLog($log, $payload)
    {
        $log = $this->applyLogPayload($payload, $log);

        $this->em->persist($log);
        $this->em->flush();

        return $log;
    }

    public function deleteLog($log)
    {
        $this->em->remove($log);
        $this->em->flush();

        return $log;
    }

    public function applyLogPayload($payload, Log $log)
    {
        $log
            ->setContext($payload['context'])
            ->setCategory($payload['category'])
            ->setAction($payload['action'])
            ->setValue($payload['value'])
            ->setUsername($this->security->getUser() ? $this->security->getUser()->getUserIdentifier() : null)
        ;

        return $log;
    }

    public function validateTelemetryPayload($payload)
    {
        if(($errors = $this->validateFields($payload, [
                'category',
                'action',
                'value',
            ])) !== true) {
            return $errors;
        }

        return true;
    }

    public function createTelemetry($payload)
    {
        $log = new Log();

        $log->setCreatedAt(new \DateTime());

        $log = $this->applyTelemetryPayload($payload, $log);

        $this->em->persist($log);
        $this->em->flush();

        return $log;
    }

    public function applyTelemetryPayload($payload, Log $log)
    {
        $fingerprint = $this->requestStack->getCurrentRequest()->getClientIp() ?: 'X';
        $fingerprint .= ':';
        $fingerprint .= $this->requestStack->getCurrentRequest()->headers->get('User-Agent') ?: 'X';
        $fingerprint .= ':';
        $fingerprint .= $this->requestStack->getCurrentRequest()->headers->get('Accept-Language') ?: 'X';

        $log
            ->setContext('Telemetry')
            ->setCategory($payload['category'])
            ->setAction($payload['action'])
            ->setValue(json_encode($payload['value']))
            ->setReferer($this->requestStack->getCurrentRequest()->headers->get('Referer'))
            ->setUsername($this->security->getUser() ? $this->security->getUser()->getUserIdentifier() : null)
            ->setFingerprint(md5($fingerprint))
        ;

        return $log;
    }

    // ================================
    // Project Editor Tracking Methods
    // ================================

    /**
     * Log that a user created a project (one-time only)
     * Called when a new project is successfully created
     * 
     * @param int $projectId The ID of the created project
     * @return Log|null The created log entry, or null if no user is authenticated
     */
    public function logProjectCreate($projectId)
    {
        $currentUser = $this->security->getUser();
        if (!$currentUser) {
            return null;
        }

        return $this->createLog([
            'context' => 'current-editor-project',
            'category' => 'created-by', 
            'action' => 'create',
            'value' => json_encode(['project_id' => (int)$projectId])
        ]);
    }

    /**
     * Log that a user saved a project (update or create last-editor log entry)
     * Called whenever a project is saved/updated
     * 
     * @param int $projectId The ID of the saved project
     * @return Log|null The updated/created log entry, or null if no user is authenticated
     */
    public function logProjectSave($projectId)
    {
        return $this->upsertProjectLog($projectId, 'last-editor', 'save');
    }

    /**
     * Log that a user started editing a project
     * Called when user opens project for editing
     * 
     * @param int $projectId The ID of the project being edited
     * @return Log|null The updated/created log entry, or null if no user is authenticated
     */
    public function logProjectEditingStart($projectId)
    {
        return $this->upsertProjectLog($projectId, 'current-editor', 'editing');
    }

    /**
     * Log a heartbeat from a user currently editing a project
     * Called periodically while user is editing to keep the session alive
     * 
     * @param int $projectId The ID of the project being edited
     * @return Log|null The updated log entry, or null if no user is authenticated
     */
    public function logProjectEditingHeartbeat($projectId)
    {
        return $this->upsertProjectLog($projectId, 'current-editor', 'heartbeat');
    }

    /**
     * Log that a user stopped editing a project
     * Called when user closes project editor or navigates away
     * 
     * @param int $projectId The ID of the project that was being edited
     * @return Log|null The updated log entry, or null if no user is authenticated
     */
    public function logProjectEditingStop($projectId)
    {
        return $this->upsertProjectLog($projectId, 'current-editor', 'stopped');
    }

    /**
     * Take over editing of a project from another user
     * This will stop any other users' current editing sessions and start a new one for the current user
     * 
     * @param int $projectId The ID of the project to take over
     * @return Log|null The updated/created log entry, or null if no user is authenticated
     */
    public function logProjectEditingTakeover($projectId)
    {
        $currentUser = $this->security->getUser();
        if (!$currentUser) {
            return null;
        }
        
        // Find all current-editor entries for this project (from any user)
        $existingEntries = $this->em->getRepository(Log::class)->findAllCurrentEditorEntriesForProject($projectId);
        
        // Mark all existing current-editor entries as 'stopped'
        foreach ($existingEntries as $entry) {
            $entry->setAction('stopped');
            $entry->setCreatedAt(new \DateTime());
            $this->em->persist($entry);
        }
        
        // Flush the updates first
        $this->em->flush();
        
        // Now create/update the entry for the current user
        return $this->upsertProjectLog($projectId, 'current-editor', 'editing');
    }

    /**
     * Internal method to update or create project log entries
     * Uses upsert pattern: update existing entry or create new one
     * 
     * @param int $projectId The project ID
     * @param string $category The log category ('last-editor', 'current-editor', etc.)
     * @param string $action The action being performed ('save', 'editing', 'heartbeat', 'stopped')
     * @return Log|null The updated/created log entry, or null if no user is authenticated
     */
    private function upsertProjectLog($projectId, $category, $action)
    {
        $currentUser = $this->security->getUser();
        if (!$currentUser) {
            return null;
        }
        
        $username = $currentUser->getUserIdentifier();
        
        // Try to find existing log entry for this user+project+category
        $existingLog = $this->em->getRepository(Log::class)->findExistingProjectLog(
            $username,
            $projectId,
            $category
        );
        
        if ($existingLog) {
            // Update existing entry with new action and timestamp
            $existingLog->setAction($action);
            $existingLog->setCreatedAt(new \DateTime());
            $this->em->persist($existingLog);
            $this->em->flush();
            
            return $existingLog;
        } else {
            // Create new entry
            return $this->createLog([
                'context' => 'current-editor-project',
                'category' => $category,
                'action' => $action,
                'value' => json_encode(['project_id' => (int)$projectId])
            ]);
        }
    }

    /**
     * Clean up stale editing sessions by marking them as 'stopped'
     * This should be called periodically to handle cases where users close their browser
     * without properly ending their editing session
     * 
     * @param int $timeThresholdMinutes How many minutes old an editing session must be to be considered stale
     * @return int Number of stale sessions that were cleaned up
     */
    public function cleanupStaleEditingSessions($timeThresholdMinutes = 5)
    {
        $staleSessions = $this->em->getRepository(Log::class)->findStaleEditingSessions($timeThresholdMinutes);
        
        $cleanedCount = 0;
        foreach ($staleSessions as $session) {
            // Mark the stale session as stopped
            $session->setAction('stopped');
            $session->setCreatedAt(new \DateTime());
            $this->em->persist($session);
            $cleanedCount++;
        }
        
        if ($cleanedCount > 0) {
            $this->em->flush();
        }
        
        return $cleanedCount;
    }

}