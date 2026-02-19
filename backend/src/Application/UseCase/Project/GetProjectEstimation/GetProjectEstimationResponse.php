<?php

declare(strict_types=1);

namespace App\Application\UseCase\Project\GetProjectEstimation;

final readonly class GetProjectEstimationResponse
{
    public function __construct(
        public float $workedHours,
        public float $estimatedHours,
        public float $remainingHours,
        public float $velocityPerDay,
        public ?int $daysRemaining,
        public ?string $estimatedCompletionDate
    ) {}
}