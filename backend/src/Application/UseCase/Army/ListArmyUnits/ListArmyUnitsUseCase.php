<?php

declare(strict_types=1);

namespace App\Application\UseCase\Army\ListArmyUnits;

use App\Application\DTO\PaintingPlanDTO;
use App\Application\DTO\UnitDTO;
use App\Application\Security\OwnershipGuard;
use App\Domain\Entity\PaintingSession;
use App\Domain\Exception\ArmyNotFoundException;
use App\Domain\Repository\ArmyRepositoryInterface;
use App\Domain\Repository\PaintingPlanRepositoryInterface;
use App\Domain\Repository\PaintingSessionRepositoryInterface;
use App\Domain\Repository\UnitComponentRepositoryInterface;
use App\Domain\Repository\UnitRepositoryInterface;

final class ListArmyUnitsUseCase
{
    public function __construct(
        private readonly ArmyRepositoryInterface $armies,
        private readonly UnitRepositoryInterface $units,
        private readonly UnitComponentRepositoryInterface $components,
        private readonly PaintingPlanRepositoryInterface $plans,
        private readonly PaintingSessionRepositoryInterface $sessions,
        private readonly OwnershipGuard $ownership,
    ) {}

    public function execute(ListArmyUnitsRequest $request): array
    {
        $army = $this->armies->findById($request->armyId()) ?? throw new ArmyNotFoundException($request->armyId()->value());
        $this->ownership->ensureOwnership($army);
        $units = $this->units->findByArmy($army->id());
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
                $workedHours = array_sum(array_map(
                    static fn(PaintingSession $session): float => $session->durationHours(),
                    $sessionsByPlan[$plan->id()->value()] ?? [],
                ));
                $planData = PaintingPlanDTO::fromEntity($plan, $workedHours)->toArray();
            }
            return UnitDTO::fromEntity($unit, $this->components->findByUnit($unit->id()), $planData);
        }, $units);
    }
}
