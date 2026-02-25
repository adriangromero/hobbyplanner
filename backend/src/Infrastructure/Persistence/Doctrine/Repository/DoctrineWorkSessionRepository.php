<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Doctrine\Repository;

use App\Domain\Entity\WorkSession;
use App\Domain\Repository\WorkSessionRepositoryInterface;
use App\Domain\ValueObject\WorkSessionId;
use App\Domain\ValueObject\ItemId;
use App\Domain\ValueObject\ProjectId;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<WorkSession>
 */
final class DoctrineWorkSessionRepository extends ServiceEntityRepository implements WorkSessionRepositoryInterface
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

    public function findGroupedByItem(ProjectId $projectId): array
    {
        $sessions = $this->createQueryBuilder('ws')
            ->where('ws.projectId = :projectId')
            ->setParameter('projectId', $projectId->value())
            ->orderBy('ws.startedAt', 'DESC')
            ->getQuery()
            ->getResult();

        $grouped = [];
        foreach ($sessions as $session) {
            $itemId             = $session->itemId()->value();
            $grouped[$itemId][] = $session;
        }

        return $grouped;
    }
}