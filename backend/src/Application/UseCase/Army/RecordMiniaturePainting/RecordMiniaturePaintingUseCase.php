<?php

declare(strict_types=1);

namespace App\Application\UseCase\Army\RecordMiniaturePainting;

use App\Application\DTO\MiniaturePaintingDTO;
use App\Application\DTO\PaintingSessionDTO;
use App\Application\DTO\UnitComponentDTO;
use App\Application\Port\TransactionPort;
use App\Application\Security\OwnershipGuard;
use App\Domain\Entity\PaintingSession;
use App\Domain\Exception\ValidationException;
use App\Domain\Exception\UnitComponentNotFoundException;
use App\Domain\Exception\UnitNotFoundException;
use App\Domain\Repository\PaintingPlanRepositoryInterface;
use App\Domain\Repository\PaintingSessionRepositoryInterface;
use App\Domain\Repository\UnitComponentRepositoryInterface;
use App\Domain\Repository\UnitRepositoryInterface;

final class RecordMiniaturePaintingUseCase
{
    public function __construct(
        private readonly UnitComponentRepositoryInterface $components,
        private readonly UnitRepositoryInterface $units,
        private readonly PaintingPlanRepositoryInterface $plans,
        private readonly PaintingSessionRepositoryInterface $sessions,
        private readonly OwnershipGuard $ownership,
        private readonly TransactionPort $transaction,
    ) {}

    public function execute(RecordMiniaturePaintingRequest $request): MiniaturePaintingDTO
    {
        $component = $this->components->findById($request->componentId())
            ?? throw new UnitComponentNotFoundException($request->componentId()->value());
        $this->ownership->ensureOwnership($component);

        $unit = $this->units->findById($component->unitId())
            ?? throw new UnitNotFoundException($component->unitId()->value());
        $this->ownership->ensureOwnership($unit);
        $plan = $this->plans->findByUnit($unit->id())
            ?? throw new ValidationException('La unidad necesita un plan de pintado para registrar el tiempo');

        $session = PaintingSession::record($plan->id(), $request->userId(), $request->durationSeconds());
        $component->adjustPainted(1);
        $this->transaction->transactional(function () use ($component, $session): void {
            $this->components->save($component);
            $this->sessions->save($session);
        });

        return new MiniaturePaintingDTO(UnitComponentDTO::fromEntity($component), PaintingSessionDTO::fromEntity($session));
    }
}
