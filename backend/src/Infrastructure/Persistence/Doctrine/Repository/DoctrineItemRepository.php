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
 * Repository Doctrine que implementa el puerto del dominio.
 *
 * Hexagonal:
 * Domain ← interface ← Infrastructure (Doctrine)
 *
 * @extends ServiceEntityRepository<Item>
 */
final class DoctrineItemRepository extends ServiceEntityRepository implements ItemRepositoryInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Item::class);
    }

    public function save(Item $item, bool $flush = true): void
    {
        $this->getEntityManager()->persist($item);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function findAll(): array
    {
        return $this->createQueryBuilder('p')
            ->getQuery()
            ->getResult();
    }

    public function findById(ItemId $id): ?Item
    {
        return $this->createQueryBuilder('i')
            ->where('i.id = :id')
            ->setParameter('id', $id->value())
            ->getQuery()
            ->getOneOrNullResult();
    }
    
    public function sumEstimatedByProject(ProjectId $projectId): float
    {
        $result = $this->createQueryBuilder('i')
            ->select('SUM(i.estimatedHours)')
            ->where('i.projectId = :projectId')
            ->setParameter('projectId', $projectId->value())
            ->getQuery()
            ->getSingleScalarResult();

        return (float) ($result ?? 0);
    }

    public function findByProject(ProjectId $id): array
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.projectId = :id')
            ->setParameter('id', $id->value())
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
}