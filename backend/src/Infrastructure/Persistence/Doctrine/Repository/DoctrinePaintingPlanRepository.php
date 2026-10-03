<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Doctrine\Repository;

use App\Domain\Entity\PaintingPlan;
use App\Domain\Repository\PaintingPlanRepositoryInterface;
use App\Domain\ValueObject\PaintingPlanId;
use App\Domain\ValueObject\UnitId;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/** @extends ServiceEntityRepository<PaintingPlan> */
final class DoctrinePaintingPlanRepository extends ServiceEntityRepository implements PaintingPlanRepositoryInterface
{
    public function __construct(ManagerRegistry $registry) { parent::__construct($registry, PaintingPlan::class); }
    public function save(PaintingPlan $plan): void { $this->getEntityManager()->persist($plan); $this->getEntityManager()->flush(); }
    public function findById(PaintingPlanId $id): ?PaintingPlan
    {
        return $this->createQueryBuilder('p')->where('p.id = :id')->setParameter('id', $id->value())->getQuery()->getOneOrNullResult();
    }
    public function findByUnit(UnitId $unitId): ?PaintingPlan
    {
        return $this->createQueryBuilder('p')->where('p.unitId = :unitId')->setParameter('unitId', $unitId->value())->getQuery()->getOneOrNullResult();
    }
    public function findByUnits(array $unitIds): array
    {
        if ($unitIds === []) return [];
        $ids = array_map(static fn(UnitId $id): string => $id->value(), $unitIds);
        return $this->createQueryBuilder('p')->where('p.unitId IN (:unitIds)')->setParameter('unitIds', $ids)->orderBy('p.createdAt', 'ASC')->getQuery()->getResult();
    }
}
