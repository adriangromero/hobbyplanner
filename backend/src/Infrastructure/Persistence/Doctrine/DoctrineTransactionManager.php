<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Doctrine;

use App\Application\Port\TransactionPort;
use Doctrine\ORM\EntityManagerInterface;

final class DoctrineTransactionManager implements TransactionPort
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
    ) {}

    public function transactional(callable $operation): mixed
    {
        return $this->entityManager->wrapInTransaction($operation);
    }
}
