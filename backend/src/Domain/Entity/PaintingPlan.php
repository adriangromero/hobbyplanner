<?php

declare(strict_types=1);

namespace App\Domain\Entity;

use App\Domain\Exception\ValidationException;
use App\Domain\Security\OwnableResource;
use App\Domain\ValueObject\PaintingPlanId;
use App\Domain\ValueObject\UnitId;
use App\Domain\ValueObject\UserId;
use DateTimeImmutable;

class PaintingPlan implements OwnableResource
{
    private DateTimeImmutable $createdAt;
    private DateTimeImmutable $updatedAt;

    private function __construct(
        private PaintingPlanId $id,
        private UnitId $unitId,
        private UserId $userId,
        private float $estimatedHours,
    ) {
        $this->assertEstimate($estimatedHours);
        $this->createdAt = new DateTimeImmutable();
        $this->updatedAt = $this->createdAt;
    }

    public static function create(UnitId $unitId, UserId $userId, float $estimatedHours): self
    {
        return new self(PaintingPlanId::create(), $unitId, $userId, $estimatedHours);
    }

    public function updateEstimate(float $hours): void
    {
        $this->assertEstimate($hours);
        $this->estimatedHours = $hours;
        $this->updatedAt = new DateTimeImmutable();
    }

    private function assertEstimate(float $hours): void
    {
        if (!is_finite($hours) || $hours <= 0) {
            throw new ValidationException('Las horas estimadas deben ser mayores a 0');
        }
    }

    public function id(): PaintingPlanId { return $this->id; }
    public function unitId(): UnitId { return $this->unitId; }
    public function userId(): UserId { return $this->userId; }
    public function ownerId(): UserId { return $this->userId; }
    public function estimatedHours(): float { return $this->estimatedHours; }
    public function createdAt(): DateTimeImmutable { return $this->createdAt; }
    public function updatedAt(): DateTimeImmutable { return $this->updatedAt; }
}
