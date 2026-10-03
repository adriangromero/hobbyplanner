<?php

declare(strict_types=1);

namespace App\Application\UseCase\Army\AddUnitComponent;

use App\Application\DTO\UnitComponentDTO;
use App\Application\Security\OwnershipGuard;
use App\Domain\Entity\UnitComponent;
use App\Domain\Exception\UnitNotFoundException;
use App\Domain\Repository\UnitComponentRepositoryInterface;
use App\Domain\Repository\UnitRepositoryInterface;

final class AddUnitComponentUseCase
{
    public function __construct(private readonly UnitRepositoryInterface $units, private readonly UnitComponentRepositoryInterface $components, private readonly OwnershipGuard $ownership) {}
    public function execute(AddUnitComponentRequest $request): UnitComponentDTO
    {
        $unit = $this->units->findById($request->unitId()) ?? throw new UnitNotFoundException($request->unitId()->value());
        $this->ownership->ensureOwnership($unit);
        $component = UnitComponent::create($unit->id(), $request->userId(), $request->label(), $request->quantityTotal());
        $this->components->save($component);
        return UnitComponentDTO::fromEntity($component);
    }
}
