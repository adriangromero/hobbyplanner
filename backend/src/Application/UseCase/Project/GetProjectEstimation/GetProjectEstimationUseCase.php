<?php

declare(strict_types=1);

namespace App\Application\UseCase\Project\GetProjectEstimation;

use App\Application\DTO\ProjectEstimationDTO;
use App\Application\Security\OwnershipGuard;
use App\Domain\Repository\ItemRepositoryInterface;
use App\Domain\Repository\ProjectRepositoryInterface;
use App\Domain\Repository\WorkSessionRepositoryInterface;
use App\Domain\Repository\UnitRepositoryInterface;
use App\Domain\Repository\PaintingPlanRepositoryInterface;
use App\Domain\Repository\PaintingSessionRepositoryInterface;
use App\Domain\Exception\ProjectNotFoundException;
use App\Domain\Service\ProjectEstimator;
use App\Domain\ValueObject\ProjectType;

final class GetProjectEstimationUseCase
{
    public function __construct(
        private readonly ProjectRepositoryInterface     $projectRepository,
        private readonly ItemRepositoryInterface        $itemRepository,
        private readonly WorkSessionRepositoryInterface $workSessionRepository,
        private readonly UnitRepositoryInterface $unitRepository,
        private readonly PaintingPlanRepositoryInterface $paintingPlanRepository,
        private readonly PaintingSessionRepositoryInterface $paintingSessionRepository,
        private readonly ProjectEstimator               $estimator,
        private readonly OwnershipGuard                 $ownershipGuard,
    ) {}

    public function execute(GetProjectEstimationRequest $request): GetProjectEstimationResponse
    {
        $projectId = $request->projectId();

        $project = $this->projectRepository->findById($projectId);

        if ($project === null) {
            throw new ProjectNotFoundException($projectId->value());
        }

        $this->ownershipGuard->ensureOwnership($project);

        // Army estimates are based exclusively on unit painting plans. Legacy
        // generic Items remain available for general projects, but must not
        // leak back into the army inventory experience.
        $isArmy = $project->type() === ProjectType::ARMY;
        $items = $isArmy ? [] : $this->itemRepository->findByProject($projectId);
        $sessions = $isArmy ? [] : $this->workSessionRepository->findByProject($projectId);
        $units = $this->unitRepository->findByProject($projectId);
        $plans = $units === [] ? [] : $this->paintingPlanRepository->findByUnits(
            array_map(static fn($unit) => $unit->id(), $units),
        );
        $sessionsByPlan = $plans === [] ? [] : $this->paintingSessionRepository->findGroupedByPlans(
            array_map(static fn($plan) => $plan->id(), $plans),
        );
        $paintingSessions = array_merge(...array_values($sessionsByPlan ?: [[]]));

        $estimation = $this->estimator->estimate(
            $project->createdAt(),
            $items,
            $sessions,
            $plans,
            $paintingSessions,
        );

        return new GetProjectEstimationResponse(
            ProjectEstimationDTO::fromValueObject($estimation)
        );
    }
}
