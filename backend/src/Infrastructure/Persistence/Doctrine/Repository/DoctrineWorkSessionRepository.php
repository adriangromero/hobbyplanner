<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Doctrine\Repository;

use App\Domain\Entity\WorkSession;
use App\Domain\Repository\WorkSessionRepositoryInterface;
use App\Domain\ValueObject\WorkSessionId;
use App\Domain\ValueObject\ItemId;
use App\Domain\ValueObject\ProjectId;
use App\Domain\ValueObject\UserId;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<WorkSession>
 */
class DoctrineWorkSessionRepository extends ServiceEntityRepository implements WorkSessionRepositoryInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, WorkSession::class);
    }

    public function save(WorkSession $session): void
    {
        $this->getEntityManager()->persist($session);
        $this->getEntityManager()->flush();
    }

    // ✅ Renombrado a findById()
    public function findById(WorkSessionId $id): ?WorkSession
    {
        return $this->createQueryBuilder('ws')
            ->where('ws.id = :id')
            ->setParameter('id', $id->value())
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function delete(WorkSession $session): void
    {
        $this->getEntityManager()->remove($session);
        $this->getEntityManager()->flush();
    }

    public function findByItem(ItemId $itemId): array
    {
        return $this->createQueryBuilder('ws')
            ->where('ws.itemId = :itemId')
            ->setParameter('itemId', $itemId->value())
            ->orderBy('ws.startedAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function findByProject(ProjectId $projectId): array
    {
        return $this->createQueryBuilder('ws')
            ->where('ws.projectId = :projectId')
            ->setParameter('projectId', $projectId->value())
            ->orderBy('ws.startedAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function findByUser(UserId $userId): array
    {
        return $this->createQueryBuilder('ws')
            ->where('ws.userId = :userId')
            ->setParameter('userId', $userId->value())
            ->orderBy('ws.startedAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function getTotalHoursByItem(ItemId $itemId): float
    {
        $result = $this->createQueryBuilder('ws')
            ->select('SUM(ws.hours) as total')
            ->where('ws.itemId = :itemId')
            ->setParameter('itemId', $itemId->value())
            ->getQuery()
            ->getSingleScalarResult();

        return (float) ($result ?? 0);
    }

    public function getTotalHoursByProject(ProjectId $projectId): float
    {
        $sessions = $this->createQueryBuilder('ws')
            ->where('ws.projectId = :projectId')
            ->andWhere('ws.endedAt IS NOT NULL')
            ->setParameter('projectId', $projectId->value())
            ->getQuery()
            ->getResult();

        $totalSeconds = 0;

        foreach ($sessions as $ws) {
            $start = $ws->startedAt()->getTimestamp();
            $end   = $ws->endedAt()->getTimestamp();

            $totalSeconds += ($end - $start);
        }

        return $totalSeconds / 3600; // horas
    }


    public function getTotalHoursByUser(UserId $userId): float
    {
        $result = $this->createQueryBuilder('ws')
            ->select('SUM(ws.hours) as total')
            ->where('ws.userId = :userId')
            ->setParameter('userId', $userId->value())
            ->getQuery()
            ->getSingleScalarResult();

        return (float) ($result ?? 0);
    }

    public function countByProject(ProjectId $projectId): int
    {
        return (int) $this->createQueryBuilder('ws')
            ->select('COUNT(ws.id)')
            ->where('ws.projectId = :projectId')
            ->setParameter('projectId', $projectId->value())
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function getHoursByDay(ProjectId $projectId, \DateTimeImmutable $since): array
    {
        $results = $this->createQueryBuilder('ws')
            ->select('DATE(ws.startedAt) as day', 'SUM(ws.hours) as hours')
            ->where('ws.projectId = :projectId')
            ->andWhere('ws.startedAt >= :since')
            ->setParameter('projectId', $projectId->value())
            ->setParameter('since', $since)
            ->groupBy('day')
            ->orderBy('day', 'ASC')
            ->getQuery()
            ->getResult();

        $hoursByDay = [];
        foreach ($results as $row) {
            $hoursByDay[$row['day']] = (float) $row['hours'];
        }

        return $hoursByDay;
    }
}