<?php

declare(strict_types=1);

namespace App\Domain\Repository;

use App\Domain\Entity\Army;
use App\Domain\ValueObject\ArmyId;
use App\Domain\ValueObject\UserId;

interface ArmyRepositoryInterface
{
    public function save(Army $army): void;
    public function findById(ArmyId $id): ?Army;
    /** @return Army[] */
    public function findByUser(UserId $userId): array;
}
