<?php

declare(strict_types=1);

namespace App\Application\UseCase\Army\UpdateUnitFormation;

use App\Application\DTO\UnitDTO;
use App\Application\Security\OwnershipGuard;
use App\Domain\Exception\UnitNotFoundException;
use App\Domain\Repository\UnitRepositoryInterface;

final class UpdateUnitFormationUseCase
{
    public function __construct(private readonly UnitRepositoryInterface $units, private readonly OwnershipGuard $ownership) {}

    public function execute(UpdateUnitFormationRequest $request): UnitDTO
    {
        $unit = $this->units->findById($request->unitId())
            ?? throw new UnitNotFoundException($request->unitId()->value());
        $this->ownership->ensureOwnership($unit);
        $unit->changeModelsPerRow($request->modelsPerRow());
        $this->units->save($unit);

        return UnitDTO::fromEntity($unit);
    }
}
