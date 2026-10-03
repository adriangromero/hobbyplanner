<?php

declare(strict_types=1);

namespace App\Application\UseCase\Army\ReorderUnits;

use App\Application\Port\TransactionPort;
use App\Application\Security\OwnershipGuard;
use App\Domain\Exception\ProjectNotFoundException;
use App\Domain\Exception\ValidationException;
use App\Domain\Repository\ProjectRepositoryInterface;
use App\Domain\Repository\UnitRepositoryInterface;
use App\Domain\ValueObject\ProjectType;

final class ReorderUnitsUseCase
{
    public function __construct(
        private readonly ProjectRepositoryInterface $projects,
        private readonly UnitRepositoryInterface $units,
        private readonly OwnershipGuard $ownership,
        private readonly TransactionPort $transaction,
    ) {}

    public function execute(ReorderUnitsRequest $request): array
    {
        $project = $this->projects->findById($request->projectId())
            ?? throw new ProjectNotFoundException($request->projectId()->value());
        $this->ownership->ensureOwnership($project);
        if ($project->type() !== ProjectType::ARMY) {
            throw new ValidationException('Solo se pueden ordenar las unidades de un ejército');
        }

        $units = $this->units->findByProject($project->id());
        $unitsById = [];
        foreach ($units as $unit) {
            $unitsById[$unit->id()->value()] = $unit;
        }

        $orderedIds = $request->unitIds();
        foreach ($orderedIds as $unitId) {
            if (!is_string($unitId)) {
                throw new ValidationException('Cada identificador del orden debe ser texto');
            }
        }
        if (count($orderedIds) !== count($units) || count(array_unique($orderedIds)) !== count($orderedIds)) {
            throw new ValidationException('El orden debe incluir cada unidad del ejército exactamente una vez');
        }

        foreach ($orderedIds as $unitId) {
            if (!isset($unitsById[$unitId])) {
                throw new ValidationException('El orden contiene una unidad ajena a este ejército');
            }
        }

        $this->transaction->transactional(function () use ($orderedIds, $unitsById): void {
            foreach ($orderedIds as $position => $unitId) {
                $unit = $unitsById[$unitId];
                $unit->reposition($position);
                $this->units->save($unit);
            }
        });

        return $this->units->findByProject($project->id());
    }
}
