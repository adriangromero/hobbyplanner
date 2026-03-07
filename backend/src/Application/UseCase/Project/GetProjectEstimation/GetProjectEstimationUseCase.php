<?php

declare(strict_types=1);

namespace App\Application\UseCase\Project\GetProjectEstimation;

use App\Application\DTO\ProjectEstimationDTO;
use App\Domain\Repository\ItemRepositoryInterface;
use App\Domain\Repository\ProjectRepositoryInterface;
use App\Domain\Repository\WorkSessionRepositoryInterface;
use App\Domain\Exception\ProjectNotFoundException;
use App\Domain\Service\ProjectEstimator;

final class GetProjectEstimationUseCase
{
    public function __construct(
        private readonly ProjectRepositoryInterface     $projectRepository,
        private readonly ItemRepositoryInterface        $itemRepository,
        private readonly WorkSessionRepositoryInterface $workSessionRepository,
        private readonly ProjectEstimator               $estimator,
    ) {}

    public function execute(GetProjectEstimationRequest $request): GetProjectEstimationResponse
    {
        $projectId = $request->projectId();

        $project = $this->projectRepository->findById($projectId);

        if ($project === null) {
            throw new ProjectNotFoundException($projectId->value());
        }

        $items    = $this->itemRepository->findByProject($projectId);
        $sessions = $this->workSessionRepository->findByProject($projectId);

        $estimation = $this->estimator->estimate(
            $project->createdAt(),
            $items,
            $sessions
        );

        return new GetProjectEstimationResponse(
            ProjectEstimationDTO::fromValueObject($estimation)
        );
    }
}