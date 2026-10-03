<?php

declare(strict_types=1);

namespace App\Application\UseCase\Army\UpdatePaintingSession;

use App\Application\DTO\PaintingSessionDTO;
use App\Application\Security\OwnershipGuard;
use App\Domain\Exception\PaintingSessionNotFoundException;
use App\Domain\Repository\PaintingSessionRepositoryInterface;

final class UpdatePaintingSessionUseCase
{
    public function __construct(
        private readonly PaintingSessionRepositoryInterface $sessions,
        private readonly OwnershipGuard $ownership,
    ) {}

    public function execute(UpdatePaintingSessionRequest $request): PaintingSessionDTO
    {
        $session = $this->sessions->findById($request->sessionId())
            ?? throw new PaintingSessionNotFoundException($request->sessionId()->value());
        $this->ownership->ensureOwnership($session);
        $session->updateDuration($request->durationSeconds());
        $this->sessions->save($session);

        return PaintingSessionDTO::fromEntity($session);
    }
}
