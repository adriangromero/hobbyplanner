<?php

declare(strict_types=1);

namespace App\Application\UseCase\Army\ListArmyUnits;

use App\Application\DTO\PaintingPlanDTO;
use App\Application\DTO\ProjectEstimationDTO;
use App\Application\DTO\UnitDTO;
use App\Application\Security\OwnershipGuard;
use App\Domain\Entity\PaintingSession;
use App\Domain\Exception\ProjectNotFoundException;
use App\Domain\Repository\ProjectRepositoryInterface;
use App\Domain\Repository\PaintingPlanRepositoryInterface;
use App\Domain\Repository\PaintingSessionRepositoryInterface;
use App\Domain\Repository\UnitComponentRepositoryInterface;
use App\Domain\Repository\UnitRepositoryInterface;
use App\Domain\Service\ProjectEstimator;
use App\Domain\ValueObject\ProjectType;

final class ListArmyUnitsUseCase
{
    public function __construct(
        private readonly ProjectRepositoryInterface $projects,
        private readonly UnitRepositoryInterface $units,
        private readonly UnitComponentRepositoryInterface $components,
        private readonly PaintingPlanRepositoryInterface $plans,
        private readonly PaintingSessionRepositoryInterface $sessions,
        private readonly ProjectEstimator $estimator,
        private readonly OwnershipGuard $ownership,
    ) {}

    public function execute(ListArmyUnitsRequest $request): array
    {
        $project = $this->projects->findById($request->projectId()) ?? throw new ProjectNotFoundException($request->projectId()->value());
        $this->ownership->ensureOwnership($project);
        if ($project->type() !== ProjectType::ARMY) {
            return [];
        }
        $units = $this->units->findByProject($project->id());
        $plans = $this->plans->findByUnits(array_map(static fn($unit) => $unit->id(), $units));
        $sessionsByPlan = $this->sessions->findGroupedByPlans(array_map(static fn($plan) => $plan->id(), $plans));
        $planByUnit = [];
        foreach ($plans as $plan) {
            $planByUnit[$plan->unitId()->value()] = $plan;
        }

        return array_map(function ($unit) use ($planByUnit, $sessionsByPlan): UnitDTO {
            $plan = $planByUnit[$unit->id()->value()] ?? null;
            $planData = null;
            if ($plan !== null) {
                $planSessions = $sessionsByPlan[$plan->id()->value()] ?? [];
                $workedHours = array_sum(array_map(
                    static fn(PaintingSession $session): float => $session->durationHours(),
                    $planSessions,
                ));
                $unitEstimation = $this->estimator->estimate($plan->createdAt(), [], [], [$plan], $planSessions);
                $planData = PaintingPlanDTO::fromEntity(
                    $plan,
                    $workedHours,
                    ProjectEstimationDTO::fromValueObject($unitEstimation),
                    count($planSessions),
                )->toArray();
            }
            return UnitDTO::fromEntity($unit, $this->components->findByUnit($unit->id()), $planData);
        }, $units);
    }
}
