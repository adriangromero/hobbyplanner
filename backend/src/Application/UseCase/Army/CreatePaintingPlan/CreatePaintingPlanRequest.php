<?php

declare(strict_types=1);
namespace App\Application\UseCase\Army\CreatePaintingPlan;
use App\Domain\ValueObject\UnitId;
use App\Domain\ValueObject\UserId;
final class CreatePaintingPlanRequest
{
    public function __construct(private readonly string $unitId, private readonly string $userId, private readonly float $estimatedHours) {}
    public function unitId(): UnitId { return UnitId::fromString($this->unitId); }
    public function userId(): UserId { return UserId::fromString($this->userId); }
    public function estimatedHours(): float { return $this->estimatedHours; }
}
