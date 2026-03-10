<?php

declare(strict_types=1);

namespace App\Application\UseCase\WorkSession\FinishWorkSession;

use App\Application\DTO\WorkSessionDTO;
use App\Application\Security\OwnershipGuard;
use App\Domain\Exception\WorkSessionNotFoundException;
use App\Domain\Repository\WorkSessionRepositoryInterface;

final class FinishWorkSessionUseCase
{
    public function __construct(
        private readonly WorkSessionRepositoryInterface $sessionRepository,
        private readonly OwnershipGuard                 $ownershipGuard,
    ) {}

    public function execute(FinishWorkSessionRequest $request): FinishWorkSessionResponse
    {
        $session = $this->sessionRepository->findById($request->sessionId());

        if ($session === null) {
            throw new WorkSessionNotFoundException($request->sessionId()->value());
        }

        $this->ownershipGuard->ensureOwnership($session);

        $session->finish();

        $this->sessionRepository->save($session);

        return new FinishWorkSessionResponse(
            WorkSessionDTO::fromEntity($session)
        );
    }
}
