<?php

namespace App\Repository;

use App\Entity\CommunitySubmission;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<CommunitySubmission>
 *
 * @method CommunitySubmission|null find($id, $lockMode = null, $lockVersion = null)
 * @method CommunitySubmission|null findOneBy(array $criteria, array $orderBy = null)
 * @method CommunitySubmission[]    findAll()
 * @method CommunitySubmission[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CommunitySubmissionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, CommunitySubmission::class);
    }
} 