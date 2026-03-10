<?php

declare(strict_types=1);

namespace App\Application\UseCase\WorkSession\DeleteWorkSession;

use App\Application\Security\OwnershipGuard;
use App\Domain\Exception\WorkSessionNotFoundException;
use App\Domain\Repository\WorkSessionRepositoryInterface;

final class DeleteWorkSessionUseCase
{
    public function __construct(
        private readonly WorkSessionRepositoryInterface $sessionRepository,
        private readonly OwnershipGuard                 $ownershipGuard,
    ) {}

    public function execute(DeleteWorkSessionRequest $request): void
    {
        $session = $this->sessionRepository->findById($request->sessionId());

        if ($session === null) {
            throw new WorkSessionNotFoundException($request->sessionId()->value());
        }

        $this->ownershipGuard->ensureOwnership($session);

        $this->sessionRepository->delete($session);
    }
}
