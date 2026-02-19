<?php

declare(strict_types=1);

namespace App\Domain\ValueObject;

use DateTimeImmutable;

final class ProjectEstimation
{
    public function __construct(
        private ?DateTimeImmutable $startDate,
        private float $estimatedHours,
        private float $workedHours,
        private float $remainingHours,
        private float $velocityPerDay,
        private ?int $daysRemaining,
        private ?DateTimeImmutable $estimatedCompletionDate
    ) {}

    public function startDate(): ?DateTimeImmutable
    {
        return $this->startDate;
    }

    public function estimatedHours(): float
    {
        return $this->estimatedHours;
    }

    public function workedHours(): float
    {
        return $this->workedHours;
    }

    public function remainingHours(): float
    {
        return $this->remainingHours;
    }

    public function velocityPerDay(): float
    {
        return $this->velocityPerDay;
    }

    public function daysRemaining(): ?int
    {
        return $this->daysRemaining;
    }

    public function estimatedCompletionDate(): ?DateTimeImmutable
    {
        return $this->estimatedCompletionDate;
    }

    public function toArray(): array
    {
        return [
            'startDate' => $this->startDate?->format('Y-m-d'),
            'estimatedHours' => $this->estimatedHours,
            'workedHours' => $this->workedHours,
            'remainingHours' => $this->remainingHours,
            'velocityPerDay' => $this->velocityPerDay,
            'daysRemaining' => $this->daysRemaining,
            'estimatedCompletionDate' => $this->estimatedCompletionDate?->format('Y-m-d'),
        ];
    }
}
