<?php

declare(strict_types=1);

namespace App\Domain\ValueObject;

use DateTimeImmutable;

final class ProjectEstimation
{
    public function __construct(
        private ?DateTimeImmutable $startDate,
        private float              $estimatedHours,
        private float              $workedHours,
        private float              $remainingHours,
        private float              $velocityPerActiveDay,
        private int                $activeDays,
        private float              $frequencyDaysPerWeek,
        private ?int               $activeDaysRemaining,
        private ?int               $daysRemaining,
        private ?DateTimeImmutable $estimatedCompletionDate,
    ) {}

    public function startDate(): ?DateTimeImmutable           { return $this->startDate; }
    public function estimatedHours(): float                   { return $this->estimatedHours; }
    public function workedHours(): float                      { return $this->workedHours; }
    public function remainingHours(): float                   { return $this->remainingHours; }
    public function velocityPerActiveDay(): float              { return $this->velocityPerActiveDay; }
    public function activeDays(): int                         { return $this->activeDays; }
    public function frequencyDaysPerWeek(): float              { return $this->frequencyDaysPerWeek; }
    public function activeDaysRemaining(): ?int               { return $this->activeDaysRemaining; }
    public function daysRemaining(): ?int                     { return $this->daysRemaining; }
    public function estimatedCompletionDate(): ?DateTimeImmutable { return $this->estimatedCompletionDate; }
}
