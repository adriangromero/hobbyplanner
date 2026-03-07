<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Doctrine\Repository;

use App\Domain\Entity\Item;
use App\Domain\Repository\ItemRepositoryInterface;
use App\Domain\ValueObject\ItemId;
use App\Domain\ValueObject\ProjectId;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Item>
 */
final class DoctrineItemRepository extends ServiceEntityRepository implements ItemRepositoryInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Item::class);
    }

    public function save(Item $item): void
    {
        $this->getEntityManager()->persist($item);
        $this->getEntityManager()->flush();
    }

    public function findById(ItemId $id): ?Item
    {
        return $this->createQueryBuilder('i')
            ->where('i.id = :id')
            ->setParameter('id', $id->value())
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function findByProject(ProjectId $projectId): array
    {
        return $this->createQueryBuilder('i')
            ->where('i.projectId = :projectId')
            ->setParameter('projectId', $projectId->value())
            ->getQuery()
            ->getResult();
    }

    public function getTotalEstimatedHoursByProject(ProjectId $projectId): float
    {
        $result = $this->createQueryBuilder('i')
            ->select('SUM(i.estimatedHours)')
            ->where('i.projectId = :projectId')
            ->setParameter('projectId', $projectId->value())
            ->getQuery()
            ->getSingleScalarResult();

        return (float) ($result ?? 0);
    }

    public function countByProject(ProjectId $projectId): int
    {
        return (int) $this->createQueryBuilder('i')
            ->select('COUNT(i.id)')
            ->where('i.projectId = :projectId')
            ->setParameter('projectId', $projectId->value())
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function delete(Item $item): void
    {
        $this->getEntityManager()->remove($item);
        $this->getEntityManager()->flush();
    }

    public function deleteByProject(ProjectId $projectId): void
    {
        $this->createQueryBuilder('i')
            ->delete()
            ->where('i.projectId = :projectId')
            ->setParameter('projectId', $projectId->value())
            ->getQuery()
            ->execute();
    }
}