<?php

declare(strict_types=1);

namespace App\Application\UseCase\Army\UpdatePaintingPlan;

use App\Domain\ValueObject\PaintingPlanId;
use App\Domain\ValueObject\UserId;

final class UpdatePaintingPlanRequest
{
    public function __construct(
        private readonly string $planId,
        private readonly string $userId,
        private readonly float $estimatedHours,
    ) {}

    public function planId(): PaintingPlanId { return PaintingPlanId::fromString($this->planId); }
    public function userId(): UserId { return UserId::fromString($this->userId); }
    public function estimatedHours(): float { return $this->estimatedHours; }
}
