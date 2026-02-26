<?php

declare(strict_types=1);

namespace App\Application\UseCase\WorkSession\FinishWorkSession;

use App\Application\DTO\WorkSessionDTO;
use App\Domain\Exception\WorkSessionNotFoundException;
use App\Domain\Repository\WorkSessionRepositoryInterface;

final class FinishWorkSessionUseCase
{
    public function __construct(
        private readonly WorkSessionRepositoryInterface $sessionRepository,
    ) {}

    public function execute(FinishWorkSessionRequest $request): FinishWorkSessionResponse
    {
        $session = $this->sessionRepository->findById($request->sessionId());

        if ($session === null) {
            throw new WorkSessionNotFoundException($request->sessionId()->value());
        }

        $session->finish();

        $this->sessionRepository->save($session);

        return new FinishWorkSessionResponse(
            WorkSessionDTO::fromEntity($session)
        );
    }
}