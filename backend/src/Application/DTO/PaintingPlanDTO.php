<?php

declare(strict_types=1);

namespace App\Application\DTO;

use App\Domain\Entity\PaintingPlan;

final class PaintingPlanDTO
{
    private function __construct(
        private string $id,
        private string $unitId,
        private float $estimatedHours,
        private float $workedHours,
        private float $remainingHours,
        private int $sessionCount,
        private ?ProjectEstimationDTO $estimation,
    ) {}

    public static function fromEntity(PaintingPlan $plan, float $workedHours = 0.0, ?ProjectEstimationDTO $estimation = null, int $sessionCount = 0): self
    {
        return new self($plan->id()->value(), $plan->unitId()->value(), $plan->estimatedHours(), $workedHours, max(0.0, $plan->estimatedHours() - $workedHours), $sessionCount, $estimation);
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'unitId' => $this->unitId,
            'estimatedHours' => $this->estimatedHours,
            'workedHours' => $this->workedHours,
            'remainingHours' => $this->remainingHours,
            'sessionCount' => $this->sessionCount,
            'estimation' => $this->estimation?->toArray(),
        ];
    }
}
