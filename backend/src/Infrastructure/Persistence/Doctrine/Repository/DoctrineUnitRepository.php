<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Doctrine\Repository;

use App\Domain\Entity\Unit;
use App\Domain\Repository\UnitRepositoryInterface;
use App\Domain\ValueObject\ProjectId;
use App\Domain\ValueObject\UnitId;
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
        return $this->createQueryBuilder('u')->where('u.projectId = :projectId')->setParameter('projectId', $projectId->value())->orderBy('u.name', 'ASC')->getQuery()->getResult();
    }
}
