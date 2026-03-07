<?php

declare(strict_types=1);

namespace App\Application\UseCase\WorkSession\StartWorkSession;

use App\Application\DTO\WorkSessionDTO;
use App\Domain\Entity\WorkSession;
use App\Domain\Repository\WorkSessionRepositoryInterface;
use App\Domain\ValueObject\WorkSessionId;
use App\Domain\Repository\ItemRepositoryInterface;
use App\Domain\Repository\ProjectRepositoryInterface;
use App\Domain\Exception\ActiveSessionExistsException;
use App\Domain\Exception\ItemNotFoundException;
use App\Domain\Exception\ProjectNotFoundException;

final class StartWorkSessionUseCase
{
    public function __construct(
        private readonly WorkSessionRepositoryInterface $sessionRepository,
        private readonly ItemRepositoryInterface        $itemRepository,
        private readonly ProjectRepositoryInterface     $projectRepository,
    ) {}

    public function execute(StartWorkSessionRequest $request): StartWorkSessionResponse
    {
        $activeSession = $this->sessionRepository->findActiveByUser($request->userId());

        if ($activeSession !== null) {
            throw new ActiveSessionExistsException();
        }

        $item = $this->itemRepository->findById($request->itemId());

        if ($item === null) {
            throw new ItemNotFoundException($request->itemId()->value());
        }

        $project = $this->projectRepository->findById($request->projectId());

        if ($project === null) {
            throw new ProjectNotFoundException($request->projectId()->value());
        }

        $session = new WorkSession(
            WorkSessionId::create(),
            $request->projectId(),
            $request->itemId(),
            $request->userId(),
            new \DateTimeImmutable(),
            null,
        );

        $this->sessionRepository->save($session);

        return new StartWorkSessionResponse(
            WorkSessionDTO::fromEntity($session)
        );
    }
}