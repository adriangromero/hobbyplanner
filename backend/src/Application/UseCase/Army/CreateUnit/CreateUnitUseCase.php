<?php

declare(strict_types=1);

namespace App\Application\UseCase\Army\CreateUnit;

use App\Application\DTO\UnitDTO;
use App\Application\Security\OwnershipGuard;
use App\Domain\Entity\Unit;
use App\Domain\Exception\ArmyNotFoundException;
use App\Domain\Repository\ArmyRepositoryInterface;
use App\Domain\Repository\UnitRepositoryInterface;

final class CreateUnitUseCase
{
    public function __construct(private readonly ArmyRepositoryInterface $armies, private readonly UnitRepositoryInterface $units, private readonly OwnershipGuard $ownership) {}
    public function execute(CreateUnitRequest $request): UnitDTO
    {
        $army = $this->armies->findById($request->armyId()) ?? throw new ArmyNotFoundException($request->armyId()->value());
        $this->ownership->ensureOwnership($army);
        $unit = Unit::create($army->id(), $request->userId(), $request->name());
        $this->units->save($unit);
        return UnitDTO::fromEntity($unit);
    }
}
