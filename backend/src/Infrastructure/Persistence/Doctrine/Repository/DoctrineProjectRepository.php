<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Doctrine\Repository;

use App\Domain\Entity\Project;
use App\Domain\Repository\ProjectRepositoryInterface;
use App\Domain\ValueObject\ProjectId;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * Repository Doctrine que implementa el puerto del dominio.
 *
 * Hexagonal:
 * Domain ← interface ← Infrastructure (Doctrine)
 *
 * @extends ServiceEntityRepository<User>
 */
final class DoctrineProjectRepository extends ServiceEntityRepository implements ProjectRepositoryInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Project::class);
    }

    public function save(Project $project, bool $flush = true): void
    {
        $this->getEntityManager()->persist($project);

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

    public function findById(ProjectId $id): ?Project
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.id = :id')
            ->setParameter('id', $id->value())
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function delete(Project $project, bool $flush = true): void
    {
        $this->getEntityManager()->remove($project);
        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }
}
