<?php

declare(strict_types=1);

namespace App\Application\UseCase\Army\UpdateUnitCategory;

use App\Application\DTO\UnitDTO;
use App\Application\Security\OwnershipGuard;
use App\Domain\Exception\UnitNotFoundException;
use App\Domain\Repository\UnitRepositoryInterface;

final class UpdateUnitCategoryUseCase
{
    public function __construct(private readonly UnitRepositoryInterface $units, private readonly OwnershipGuard $ownership) {}

    public function execute(UpdateUnitCategoryRequest $request): UnitDTO
    {
        $unit = $this->units->findById($request->unitId())
            ?? throw new UnitNotFoundException($request->unitId()->value());
        $this->ownership->ensureOwnership($unit);
        $unit->changeCategory($request->category());
        $this->units->save($unit);

        return UnitDTO::fromEntity($unit);
    }
}
