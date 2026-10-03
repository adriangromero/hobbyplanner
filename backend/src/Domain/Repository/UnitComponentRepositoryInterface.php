<?php

declare(strict_types=1);

namespace App\Domain\Repository;

use App\Domain\Entity\UnitComponent;
use App\Domain\ValueObject\UnitComponentId;
use App\Domain\ValueObject\UnitId;

interface UnitComponentRepositoryInterface
{
    public function save(UnitComponent $component): void;
    public function findById(UnitComponentId $id): ?UnitComponent;
    /** @return UnitComponent[] */
    public function findByUnit(UnitId $unitId): array;
    public function remove(UnitComponent $component): void;
}
