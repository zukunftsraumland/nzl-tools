<?php

namespace App\Repository;

use App\Entity\Log;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * Repository for Log entity with specialized methods for editor tracking
 * Used for the current-editor-project feature
 */
class LogRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Log::class);
    }

    /**
     * Get all current editors across all projects
     * Returns log entries for users currently editing (recent heartbeats without 'stopped')
     * 
     * @param int $timeThresholdMinutes How many minutes back to look for active editors
     * @return Log[] Array of log entries for current editors
     */
    public function getCurrentEditors($timeThresholdMinutes = 2)
    {
        $threshold = new \DateTime();
        $threshold->modify("-{$timeThresholdMinutes} minutes");
        
        return $this->createQueryBuilder('l')
            ->where('l.context = :context')
            ->andWhere('l.category = :category')
            ->andWhere('l.action IN (:actions)')
            ->andWhere('l.createdAt >= :threshold')
            ->setParameter('context', 'current-editor-project')
            ->setParameter('category', 'current-editor')
            ->setParameter('actions', ['editing', 'heartbeat'])
            ->setParameter('threshold', $threshold)
            ->getQuery()
            ->getResult();
    }

    /**
     * Get current editors for a specific project
     * Optionally exclude a specific username (useful to exclude current user)
     * 
     * @param int $projectId The project ID to check
     * @param string|null $excludeUsername Username to exclude from results
     * @param int $timeThresholdMinutes How many minutes back to look for active editors
     * @return Log[] Array of log entries for current editors of this project
     */
    public function getCurrentEditorsForProject($projectId, $excludeUsername = null, $timeThresholdMinutes = 2)
    {
        $threshold = new \DateTime();
        $threshold->modify("-{$timeThresholdMinutes} minutes");
        
        $qb = $this->createQueryBuilder('l')
            ->where('l.context = :context')
            ->andWhere('l.category = :category')
            ->andWhere('l.action IN (:actions)')
            ->andWhere('l.value = :value')
            ->andWhere('l.createdAt >= :threshold')
            ->setParameter('context', 'current-editor-project')
            ->setParameter('category', 'current-editor')
            ->setParameter('actions', ['editing', 'heartbeat'])
            ->setParameter('value', json_encode(['project_id' => (int)$projectId]))
            ->setParameter('threshold', $threshold);
        
        if ($excludeUsername) {
            $qb->andWhere('l.username != :excludeUsername')
               ->setParameter('excludeUsername', $excludeUsername);
        }
        
        return $qb->getQuery()->getResult();
    }

    /**
     * Get the last editor for a specific project
     * Returns the most recent save log entry for the project
     * 
     * @param int $projectId The project ID to check
     * @return Log|null The log entry for the last editor, or null if none found
     */
    public function getLastEditorForProject($projectId)
    {
        return $this->createQueryBuilder('l')
            ->where('l.context = :context')
            ->andWhere('l.category = :category')
            ->andWhere('l.action = :action')
            ->andWhere('l.value = :value')
            ->orderBy('l.createdAt', 'DESC')
            ->setMaxResults(1)
            ->setParameter('context', 'current-editor-project')
            ->setParameter('category', 'last-editor')
            ->setParameter('action', 'save')
            ->setParameter('value', json_encode(['project_id' => (int)$projectId]))
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * Get the creator of a specific project
     * Returns the log entry for who created the project
     * 
     * @param int $projectId The project ID to check
     * @return Log|null The log entry for the project creator, or null if none found
     */
    public function getCreatedByForProject($projectId)
    {
        return $this->createQueryBuilder('l')
            ->where('l.context = :context')
            ->andWhere('l.category = :category')
            ->andWhere('l.action = :action')
            ->andWhere('l.value = :value')
            ->setParameter('context', 'current-editor-project')
            ->setParameter('category', 'created-by')
            ->setParameter('action', 'create')
            ->setParameter('value', json_encode(['project_id' => (int)$projectId]))
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * Find an existing log entry for a user, project, and category combination
     * Used by the upsert logic in LogService
     * 
     * @param string $username The username to search for
     * @param int $projectId The project ID
     * @param string $category The log category ('current-editor', 'last-editor', etc.)
     * @return Log|null The existing log entry, or null if none found
     */
    public function findExistingProjectLog($username, $projectId, $category)
    {
        return $this->createQueryBuilder('l')
            ->where('l.context = :context')
            ->andWhere('l.category = :category')
            ->andWhere('l.username = :username')
            ->andWhere('l.value = :value')
            ->setParameter('context', 'current-editor-project')
            ->setParameter('category', $category)
            ->setParameter('username', $username)
            ->setParameter('value', json_encode(['project_id' => (int)$projectId]))
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * Find all current-editor entries for a specific project (from any user)
     * Used to clean up when a new user starts editing
     * 
     * @param int $projectId The project ID
     * @return Log[] Array of all current-editor log entries for this project
     */
    public function findAllCurrentEditorEntriesForProject($projectId)
    {
        return $this->createQueryBuilder('l')
            ->where('l.context = :context')
            ->andWhere('l.category = :category')
            ->andWhere('l.value = :value')
            ->andWhere('l.action IN (:actions)')
            ->setParameter('context', 'current-editor-project')
            ->setParameter('category', 'current-editor')
            ->setParameter('value', json_encode(['project_id' => (int)$projectId]))
            ->setParameter('actions', ['editing', 'heartbeat'])
            ->getQuery()
            ->getResult();
    }

    /**
     * Find stale editing sessions that should be marked as stopped
     * Returns editing/heartbeat entries older than the specified threshold
     * 
     * @param int $timeThresholdMinutes How many minutes back to consider as "stale"
     * @return Log[] Array of stale log entries that should be marked as stopped
     */
    public function findStaleEditingSessions($timeThresholdMinutes = 5)
    {
        $threshold = new \DateTime();
        $threshold->modify("-{$timeThresholdMinutes} minutes");
        
        return $this->createQueryBuilder('l')
            ->where('l.context = :context')
            ->andWhere('l.category = :category')
            ->andWhere('l.action IN (:actions)')
            ->andWhere('l.createdAt < :threshold')
            ->setParameter('context', 'current-editor-project')
            ->setParameter('category', 'current-editor')
            ->setParameter('actions', ['editing', 'heartbeat'])
            ->setParameter('threshold', $threshold)
            ->getQuery()
            ->getResult();
    }
} 