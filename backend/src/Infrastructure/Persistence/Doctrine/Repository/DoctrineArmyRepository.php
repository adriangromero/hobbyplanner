<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Doctrine\Repository;

use App\Domain\Entity\Army;
use App\Domain\Repository\ArmyRepositoryInterface;
use App\Domain\ValueObject\ArmyId;
use App\Domain\ValueObject\UserId;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/** @extends ServiceEntityRepository<Army> */
final class DoctrineArmyRepository extends ServiceEntityRepository implements ArmyRepositoryInterface
{
    public function __construct(ManagerRegistry $registry) { parent::__construct($registry, Army::class); }
    public function save(Army $army): void { $this->getEntityManager()->persist($army); $this->getEntityManager()->flush(); }
    public function findById(ArmyId $id): ?Army
    {
        return $this->createQueryBuilder('a')->where('a.id = :id')->setParameter('id', $id->value())->getQuery()->getOneOrNullResult();
    }
    public function findByUser(UserId $userId): array
    {
        return $this->createQueryBuilder('a')->where('a.userId = :userId')->setParameter('userId', $userId->value())->orderBy('a.name', 'ASC')->getQuery()->getResult();
    }
}
