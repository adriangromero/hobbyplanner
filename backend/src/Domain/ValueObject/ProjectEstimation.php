<?php

declare(strict_types=1);

namespace App\Domain\ValueObject;

use DateTimeImmutable;

final class ProjectEstimation
{
    private function __construct(
        private readonly ?DateTimeImmutable $startDate,
        private readonly float              $estimatedHours,
        private readonly float              $workedHours,
        private readonly float              $remainingHours,
        private readonly float              $velocityPerActiveDay,
        private readonly int                $activeDays,
        private readonly float              $frequencyDaysPerWeek,
        private readonly ?int               $activeDaysRemaining,
        private readonly ?int               $daysRemaining,
        private readonly ?DateTimeImmutable $estimatedCompletionDate,
    ) {}

    public static function create(
        ?DateTimeImmutable $startDate,
        float              $estimatedHours,
        float              $workedHours,
        float              $remainingHours,
        float              $velocityPerActiveDay,
        int                $activeDays,
        float              $frequencyDaysPerWeek,
        ?int               $activeDaysRemaining,
        ?int               $daysRemaining,
        ?DateTimeImmutable $estimatedCompletionDate,
    ): self {
        return new self(
            $startDate,
            max(0.0, $estimatedHours),
            max(0.0, $workedHours),
            max(0.0, $remainingHours),
            max(0.0, $velocityPerActiveDay),
            max(0, $activeDays),
            max(0.0, min(7.0, $frequencyDaysPerWeek)),
            $activeDaysRemaining !== null ? max(0, $activeDaysRemaining) : null,
            $daysRemaining !== null ? max(0, $daysRemaining) : null,
            $estimatedCompletionDate,
        );
    }

    public function startDate(): ?DateTimeImmutable           { return $this->startDate; }
    public function estimatedHours(): float                   { return $this->estimatedHours; }
    public function workedHours(): float                      { return $this->workedHours; }
    public function remainingHours(): float                   { return $this->remainingHours; }
    public function velocityPerActiveDay(): float             { return $this->velocityPerActiveDay; }
    public function activeDays(): int                         { return $this->activeDays; }
    public function frequencyDaysPerWeek(): float             { return $this->frequencyDaysPerWeek; }
    public function activeDaysRemaining(): ?int               { return $this->activeDaysRemaining; }
    public function daysRemaining(): ?int                     { return $this->daysRemaining; }
    public function estimatedCompletionDate(): ?DateTimeImmutable { return $this->estimatedCompletionDate; }
}
