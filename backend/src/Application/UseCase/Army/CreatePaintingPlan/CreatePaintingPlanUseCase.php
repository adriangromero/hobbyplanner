<?php

declare(strict_types=1);
namespace App\Application\UseCase\Army\CreatePaintingPlan;
use App\Application\DTO\PaintingPlanDTO;
use App\Application\Security\OwnershipGuard;
use App\Domain\Entity\PaintingPlan;
use App\Domain\Exception\UnitNotFoundException;
use App\Domain\Exception\ValidationException;
use App\Domain\Repository\PaintingPlanRepositoryInterface;
use App\Domain\Repository\UnitRepositoryInterface;
final class CreatePaintingPlanUseCase
{
    public function __construct(private readonly UnitRepositoryInterface $units, private readonly PaintingPlanRepositoryInterface $plans, private readonly OwnershipGuard $ownership) {}
    public function execute(CreatePaintingPlanRequest $request): PaintingPlanDTO
    {
        $unit = $this->units->findById($request->unitId()) ?? throw new UnitNotFoundException($request->unitId()->value());
        $this->ownership->ensureOwnership($unit);
        if ($this->plans->findByUnit($unit->id()) !== null) {
            throw new ValidationException('Esta unidad ya tiene un plan de pintado');
        }
        $plan = PaintingPlan::create($unit->id(), $request->userId(), $request->estimatedHours());
        $this->plans->save($plan);
        return PaintingPlanDTO::fromEntity($plan);
    }
}
