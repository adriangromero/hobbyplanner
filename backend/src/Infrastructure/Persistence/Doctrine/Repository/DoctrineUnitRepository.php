<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Doctrine\Repository;

use App\Domain\Entity\Unit;
use App\Domain\Repository\UnitRepositoryInterface;
use App\Domain\ValueObject\ProjectId;
use App\Domain\ValueObject\UnitId;
use Doctrine\DBAL\ArrayParameterType;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/** @extends ServiceEntityRepository<Unit> */
final class DoctrineUnitRepository extends ServiceEntityRepository implements UnitRepositoryInterface
{
    public function __construct(ManagerRegistry $registry) { parent::__construct($registry, Unit::class); }
    public function save(Unit $unit): void { $this->getEntityManager()->persist($unit); $this->getEntityManager()->flush(); }
    public function findById(UnitId $id): ?Unit
    {
        return $this->createQueryBuilder('u')->where('u.id = :id')->setParameter('id', $id->value())->getQuery()->getOneOrNullResult();
    }
    public function findByProject(ProjectId $projectId): array
    {
        return $this->createQueryBuilder('u')
            ->where('u.projectId = :projectId')
            ->setParameter('projectId', $projectId->value())
            ->orderBy('u.position', 'ASC')
            ->addOrderBy('u.createdAt', 'ASC')
            ->addOrderBy('u.name', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function inventorySummaryByProjects(array $projectIds): array
    {
        if ($projectIds === []) {
            return [];
        }

        $rows = $this->getEntityManager()->getConnection()->executeQuery(
            'SELECT u.project_id, COUNT(DISTINCT u.id) AS unit_count, COALESCE(SUM(c.quantity_total), 0) AS miniature_count
             FROM units u
             LEFT JOIN unit_components c ON c.unit_id = u.id
             WHERE u.project_id IN (?)
             GROUP BY u.project_id',
            [array_map(static fn(ProjectId $id): string => $id->value(), $projectIds)],
            [ArrayParameterType::STRING],
        )->fetchAllAssociative();

        $summaries = [];
        foreach ($rows as $row) {
            $summaries[$row['project_id']] = [
                'unitCount' => (int) $row['unit_count'],
                'miniatureCount' => (int) $row['miniature_count'],
            ];
        }

        return $summaries;
    }
}
