<?php

declare(strict_types=1);

namespace App\Domain\Repository;

use App\Domain\Entity\PaintingPlan;
use App\Domain\ValueObject\PaintingPlanId;
use App\Domain\ValueObject\UnitId;

interface PaintingPlanRepositoryInterface
{
    public function save(PaintingPlan $plan): void;
    public function findById(PaintingPlanId $id): ?PaintingPlan;
    public function findByUnit(UnitId $unitId): ?PaintingPlan;
    /** @param UnitId[] $unitIds @return PaintingPlan[] */
    public function findByUnits(array $unitIds): array;
}
