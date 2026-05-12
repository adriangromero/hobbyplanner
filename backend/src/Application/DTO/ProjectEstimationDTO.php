<?php

declare(strict_types=1);

namespace App\Application\DTO;

use App\Domain\ValueObject\ProjectEstimation;

final class ProjectEstimationDTO
{
    public function __construct(
        public readonly ?string $startDate,
        public readonly float   $estimatedHours,
        public readonly float   $workedHours,
        public readonly float   $remainingHours,
        public readonly float   $velocityPerActiveDay,
        public readonly int     $activeDays,
        public readonly float   $frequencyDaysPerWeek,
        public readonly ?int    $activeDaysRemaining,
        public readonly ?int    $daysRemaining,
        public readonly ?string $estimatedCompletionDate,
    ) {}

    public static function fromValueObject(ProjectEstimation $estimation): self
    {
        return new self(
            startDate:               $estimation->startDate()?->format('Y-m-d'),
            estimatedHours:          $estimation->estimatedHours(),
            workedHours:             $estimation->workedHours(),
            remainingHours:          $estimation->remainingHours(),
            velocityPerActiveDay:    $estimation->velocityPerActiveDay(),
            activeDays:              $estimation->activeDays(),
            frequencyDaysPerWeek:    $estimation->frequencyDaysPerWeek(),
            activeDaysRemaining:     $estimation->activeDaysRemaining(),
            daysRemaining:           $estimation->daysRemaining(),
            estimatedCompletionDate: $estimation->estimatedCompletionDate()?->format('Y-m-d'),
        );
    }

    public function toArray(): array
    {
        return [
            'startDate'               => $this->startDate,
            'estimatedHours'          => $this->estimatedHours,
            'workedHours'             => $this->workedHours,
            'remainingHours'          => $this->remainingHours,
            'velocityPerActiveDay'    => $this->velocityPerActiveDay,
            'activeDays'              => $this->activeDays,
            'frequencyDaysPerWeek'    => $this->frequencyDaysPerWeek,
            'activeDaysRemaining'     => $this->activeDaysRemaining,
            'daysRemaining'           => $this->daysRemaining,
            'estimatedCompletionDate' => $this->estimatedCompletionDate,
        ];
    }
}
