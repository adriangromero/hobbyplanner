<?php

declare(strict_types=1);

namespace App\Domain\Repository;

use App\Domain\Entity\PaintingSession;
use App\Domain\ValueObject\PaintingPlanId;

interface PaintingSessionRepositoryInterface
{
    public function save(PaintingSession $session): void;
    /** @return PaintingSession[] */
    public function findByPlan(PaintingPlanId $planId): array;
    /** @param PaintingPlanId[] $planIds @return array<string, PaintingSession[]> planId => sessions */
    public function findGroupedByPlans(array $planIds): array;
}
