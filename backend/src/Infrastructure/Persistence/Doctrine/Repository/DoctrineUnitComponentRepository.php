<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Doctrine\Repository;

use App\Domain\Entity\UnitComponent;
use App\Domain\Repository\UnitComponentRepositoryInterface;
use App\Domain\ValueObject\UnitComponentId;
use App\Domain\ValueObject\UnitId;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/** @extends ServiceEntityRepository<UnitComponent> */
final class DoctrineUnitComponentRepository extends ServiceEntityRepository implements UnitComponentRepositoryInterface
{
    public function __construct(ManagerRegistry $registry) { parent::__construct($registry, UnitComponent::class); }
    public function save(UnitComponent $component): void { $this->getEntityManager()->persist($component); $this->getEntityManager()->flush(); }
    public function findById(UnitComponentId $id): ?UnitComponent
    {
        return $this->createQueryBuilder('c')->where('c.id = :id')->setParameter('id', $id->value())->getQuery()->getOneOrNullResult();
    }
    public function findByUnit(UnitId $unitId): array
    {
        return $this->createQueryBuilder('c')->where('c.unitId = :unitId')->setParameter('unitId', $unitId->value())->orderBy('c.createdAt', 'ASC')->getQuery()->getResult();
    }
    public function remove(UnitComponent $component): void
    {
        $this->getEntityManager()->remove($component);
        $this->getEntityManager()->flush();
    }
}
