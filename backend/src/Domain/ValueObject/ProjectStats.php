<?php

declare(strict_types=1);

namespace App\Domain\ValueObject;

final readonly class ProjectStats
{
    public function __construct(
        public float $estimatedHours,
        public float $workedHours,
        public float $remainingHours,
        public int $itemsCount,
        public float $progress
    ) {}
    
    public function isOverBudget(): bool
    {
        return $this->workedHours > $this->estimatedHours;
    }
    
    public function isCompleted(): bool
    {
        return $this->progress >= 100;
    }
}