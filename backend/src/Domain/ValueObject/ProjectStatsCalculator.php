<?php

declare(strict_types=1);

namespace App\Domain\ValueObject;

use App\Domain\ValueObject\ProjectId;
use App\Domain\Repository\ItemRepositoryInterface;
use App\Domain\Repository\WorkSessionRepositoryInterface;

final readonly class ProjectStatsCalculator
{
    public function __construct(
        private ItemRepositoryInterface $itemRepository,
        private WorkSessionRepositoryInterface $workSessionRepository
    ) {}

    public function calculate(ProjectId $projectId): ProjectStats
    {
        $estimatedHours = $this->itemRepository->getTotalEstimatedHoursByProject($projectId);
        $workedHours = $this->workSessionRepository->getTotalHoursByProject($projectId);
        $itemsCount = $this->itemRepository->countByProject($projectId);
        
        return new ProjectStats(
            estimatedHours: $estimatedHours,
            workedHours: $workedHours,
            remainingHours: max(0, $estimatedHours - $workedHours),
            itemsCount: $itemsCount,
            progress: $estimatedHours > 0 ? ($workedHours / $estimatedHours) * 100 : 0
        );
    }
}