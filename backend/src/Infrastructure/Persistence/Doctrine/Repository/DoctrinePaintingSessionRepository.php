<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Doctrine\Repository;

use App\Domain\Entity\PaintingSession;
use App\Domain\Repository\PaintingSessionRepositoryInterface;
use App\Domain\ValueObject\PaintingPlanId;
use App\Domain\ValueObject\PaintingSessionId;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/** @extends ServiceEntityRepository<PaintingSession> */
final class DoctrinePaintingSessionRepository extends ServiceEntityRepository implements PaintingSessionRepositoryInterface
{
    public function __construct(ManagerRegistry $registry) { parent::__construct($registry, PaintingSession::class); }
    public function save(PaintingSession $session): void { $this->getEntityManager()->persist($session); $this->getEntityManager()->flush(); }
    public function findById(PaintingSessionId $id): ?PaintingSession
    {
        return $this->createQueryBuilder('s')->where('s.id = :id')->setParameter('id', $id->value())->getQuery()->getOneOrNullResult();
    }
    public function findByPlan(PaintingPlanId $planId): array
    {
        return $this->createQueryBuilder('s')->where('s.paintingPlanId = :planId')->setParameter('planId', $planId->value())->orderBy('s.workedAt', 'ASC')->getQuery()->getResult();
    }
    public function findGroupedByPlans(array $planIds): array
    {
        if ($planIds === []) return [];
        $ids = array_map(static fn(PaintingPlanId $id): string => $id->value(), $planIds);
        $sessions = $this->createQueryBuilder('s')
            ->where('s.paintingPlanId IN (:planIds)')
            ->setParameter('planIds', $ids)
            ->orderBy('s.workedAt', 'ASC')
            ->getQuery()
            ->getResult();
        $grouped = [];
        foreach ($sessions as $session) {
            $grouped[$session->paintingPlanId()->value()][] = $session;
        }
        return $grouped;
    }
}
