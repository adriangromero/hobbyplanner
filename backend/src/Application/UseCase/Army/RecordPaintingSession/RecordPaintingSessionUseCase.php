<?php

declare(strict_types=1);
namespace App\Application\UseCase\Army\RecordPaintingSession;
use App\Application\DTO\PaintingSessionDTO;
use App\Application\Security\OwnershipGuard;
use App\Domain\Entity\PaintingSession;
use App\Domain\Exception\PaintingPlanNotFoundException;
use App\Domain\Repository\PaintingPlanRepositoryInterface;
use App\Domain\Repository\PaintingSessionRepositoryInterface;
final class RecordPaintingSessionUseCase
{
    public function __construct(private readonly PaintingPlanRepositoryInterface $plans, private readonly PaintingSessionRepositoryInterface $sessions, private readonly OwnershipGuard $ownership) {}
    public function execute(RecordPaintingSessionRequest $request): PaintingSessionDTO
    {
        $plan = $this->plans->findById($request->planId()) ?? throw new PaintingPlanNotFoundException($request->planId()->value());
        $this->ownership->ensureOwnership($plan);
        $session = PaintingSession::record($plan->id(), $request->userId(), $request->durationSeconds());
        $this->sessions->save($session);
        return PaintingSessionDTO::fromEntity($session);
    }
}
