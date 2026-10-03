<?php

declare(strict_types=1);

namespace App\Domain\Repository;

use App\Domain\Entity\PaintingSession;
use App\Domain\ValueObject\PaintingPlanId;
use App\Domain\ValueObject\PaintingSessionId;

interface PaintingSessionRepositoryInterface
{
    public function save(PaintingSession $session): void;
    public function findById(PaintingSessionId $id): ?PaintingSession;
    /** @return PaintingSession[] */
    public function findByPlan(PaintingPlanId $planId): array;
    /** @param PaintingPlanId[] $planIds @return array<string, PaintingSession[]> planId => sessions */
    public function findGroupedByPlans(array $planIds): array;
}
