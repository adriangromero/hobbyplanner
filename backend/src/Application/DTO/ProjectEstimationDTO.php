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
        public readonly float   $velocityPerDay,
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
            velocityPerDay:          $estimation->velocityPerDay(),
            daysRemaining:           $estimation->daysRemaining(),
            estimatedCompletionDate: $estimation->estimatedCompletionDate()?->format('Y-m-d'),
        );
    }
}