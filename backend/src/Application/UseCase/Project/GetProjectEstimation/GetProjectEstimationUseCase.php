<?php

declare(strict_types=1);

namespace App\Application\UseCase\Project\GetProjectEstimation;

use App\Domain\Repository\ItemRepositoryInterface;
use App\Domain\Repository\ProjectRepositoryInterface;
use App\Domain\Repository\WorkSessionRepositoryInterface;
use App\Domain\Service\ProjectEstimator;
use App\Domain\ValueObject\ProjectEstimation;
use App\Domain\ValueObject\ProjectId;

final readonly class GetProjectEstimationUseCase
{
    public function __construct(
        private ItemRepositoryInterface $itemRepository,
        private ProjectRepositoryInterface $projectRepository,
        private WorkSessionRepositoryInterface $workSessionRepository,
        private ProjectEstimator $estimator
    ) {}

    public function execute(ProjectId $projectId): ProjectEstimation
    {
        $items = $this->itemRepository->findByProject($projectId);
        $sessions = $this->workSessionRepository->findByProject($projectId);
        $project = $this->projectRepository->findById($projectId);

        return $this->estimator->estimate($project->createdAt(), $items, $sessions );
    }
}
