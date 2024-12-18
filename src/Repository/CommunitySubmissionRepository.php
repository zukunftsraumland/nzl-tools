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

    public function findUnverifiedByToken(string $token): ?CommunitySubmission
    {
        return $this->createQueryBuilder('p')
            ->where('p.verificationToken = :token')
            ->andWhere('p.isVerified = :isVerified')
            ->setParameter('token', $token)
            ->setParameter('isVerified', false)
            ->getQuery()
            ->getOneOrNullResult();
    }
} 