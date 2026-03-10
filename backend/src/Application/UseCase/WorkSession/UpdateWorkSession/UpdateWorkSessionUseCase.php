<?php

declare(strict_types=1);

namespace App\Application\UseCase\WorkSession\UpdateWorkSession;

use App\Application\DTO\WorkSessionDTO;
use App\Application\Security\OwnershipGuard;
use App\Domain\Exception\WorkSessionNotFoundException;
use App\Domain\Repository\WorkSessionRepositoryInterface;

final class UpdateWorkSessionUseCase
{
    public function __construct(
        private readonly WorkSessionRepositoryInterface $sessionRepository,
        private readonly OwnershipGuard                 $ownershipGuard,
    ) {}

    public function execute(UpdateWorkSessionRequest $request): UpdateWorkSessionResponse
    {
        $session = $this->sessionRepository->findById($request->sessionId());

        if ($session === null) {
            throw new WorkSessionNotFoundException($request->sessionId()->value());
        }

        $this->ownershipGuard->ensureOwnership($session);

        $session->update($request->startedAt(), $request->endedAt());

        $this->sessionRepository->save($session);

        return new UpdateWorkSessionResponse(
            WorkSessionDTO::fromEntity($session)
        );
    }
}
