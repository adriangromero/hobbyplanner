<?php

declare(strict_types=1);

namespace App\Application\UseCase\Item\GetItemSessions;

use App\Application\DTO\WorkSessionDTO;
use App\Application\Security\OwnershipGuard;
use App\Domain\Entity\WorkSession;
use App\Domain\Exception\ItemNotFoundException;
use App\Domain\Repository\ItemRepositoryInterface;
use App\Domain\Repository\WorkSessionRepositoryInterface;

final class GetItemSessionsUseCase
{
    public function __construct(
        private readonly WorkSessionRepositoryInterface $sessionRepository,
        private readonly ItemRepositoryInterface        $itemRepository,
        private readonly OwnershipGuard                 $ownershipGuard,
    ) {}

    public function execute(GetItemSessionsRequest $request): GetItemSessionsResponse
    {
        $item = $this->itemRepository->findById($request->itemId());

        if ($item === null) {
            throw new ItemNotFoundException($request->itemId()->value());
        }

        $this->ownershipGuard->ensureOwnership($item);

        $sessions = $this->sessionRepository->findByItem($request->itemId());

        return new GetItemSessionsResponse(
            array_map(
                fn(WorkSession $session) => WorkSessionDTO::fromEntity($session),
                $sessions
            )
        );
    }
}
