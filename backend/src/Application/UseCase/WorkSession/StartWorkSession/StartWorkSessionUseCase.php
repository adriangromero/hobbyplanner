<?php

declare(strict_types=1);

namespace App\Application\UseCase\WorkSession\StartWorkSession;

use App\Application\DTO\WorkSessionDTO;
use App\Application\Security\OwnershipGuard;
use App\Application\Port\TransactionPort;
use App\Domain\Entity\WorkSession;
use App\Domain\Exception\ActiveSessionExistsException;
use App\Domain\Exception\ItemNotFoundException;
use App\Domain\Exception\ProjectNotFoundException;
use App\Domain\Exception\ValidationException;
use App\Domain\Repository\ItemRepositoryInterface;
use App\Domain\Repository\ProjectRepositoryInterface;
use App\Domain\Repository\WorkSessionRepositoryInterface;

final class StartWorkSessionUseCase
{
    public function __construct(
        private readonly WorkSessionRepositoryInterface $sessionRepository,
        private readonly ItemRepositoryInterface        $itemRepository,
        private readonly ProjectRepositoryInterface     $projectRepository,
        private readonly OwnershipGuard                 $ownershipGuard,
        private readonly TransactionPort                $transaction,
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

        $this->ownershipGuard->ensureOwnership($item);

        if (!$item->projectId()->equals($request->projectId())) {
            throw new ValidationException('El item no pertenece al proyecto indicado');
        }

        $project = $this->projectRepository->findById($request->projectId());

        if ($project === null) {
            throw new ProjectNotFoundException($request->projectId()->value());
        }

        $this->ownershipGuard->ensureOwnership($project);

        $session = $this->transaction->transactional(function () use ($item, $request): WorkSession {
            $session = WorkSession::startNow(
                $request->projectId(),
                $request->itemId(),
                $request->userId(),
            );

            $item->markAsInProgress();
            $this->itemRepository->save($item);
            $this->sessionRepository->save($session);

            return $session;
        });

        return new StartWorkSessionResponse(
            WorkSessionDTO::fromEntity($session)
        );
    }
}
