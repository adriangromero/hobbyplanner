<?php

declare(strict_types=1);

namespace App\Application\UseCase\Army\CompleteUnit;

use App\Application\DTO\UnitDTO;
use App\Application\Port\TransactionPort;
use App\Application\Security\OwnershipGuard;
use App\Domain\Exception\UnitNotFoundException;
use App\Domain\Exception\ValidationException;
use App\Domain\Repository\UnitComponentRepositoryInterface;
use App\Domain\Repository\UnitRepositoryInterface;

final class CompleteUnitUseCase
{
    public function __construct(
        private readonly UnitRepositoryInterface $units,
        private readonly UnitComponentRepositoryInterface $components,
        private readonly OwnershipGuard $ownership,
        private readonly TransactionPort $transaction,
    ) {}

    public function execute(CompleteUnitRequest $request): UnitDTO
    {
        $unit = $this->units->findById($request->unitId())
            ?? throw new UnitNotFoundException($request->unitId()->value());
        $this->ownership->ensureOwnership($unit);
        $components = $this->components->findByUnit($unit->id());
        if ($components === []) {
            throw new ValidationException('Añade al menos un componente antes de completar la unidad');
        }

        $this->transaction->transactional(function () use ($components): void {
            foreach ($components as $component) {
                $component->updatePainted($component->quantityTotal());
                $this->components->save($component);
            }
        });

        return UnitDTO::fromEntity($unit, $components);
    }
}
