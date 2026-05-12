<?php

declare(strict_types=1);

namespace App\Application\UseCase\Item\DeleteItem;

use App\Application\Port\TransactionPort;
use App\Application\Security\OwnershipGuard;
use App\Domain\Exception\ItemNotFoundException;
use App\Domain\Repository\ItemRepositoryInterface;
use App\Domain\Repository\WorkSessionRepositoryInterface;

final class DeleteItemUseCase
{
    public function __construct(
        private readonly ItemRepositoryInterface        $itemRepository,
        private readonly WorkSessionRepositoryInterface $sessionRepository,
        private readonly OwnershipGuard                 $ownershipGuard,
        private readonly TransactionPort                $transaction,
    ) {}

    public function execute(DeleteItemRequest $request): void
    {
        $item = $this->itemRepository->findById($request->itemId());

        if ($item === null) {
            throw new ItemNotFoundException($request->itemId()->value());
        }

        $this->ownershipGuard->ensureOwnership($item);

        $this->transaction->transactional(function () use ($request, $item): void {
            $this->sessionRepository->deleteByItem($request->itemId());
            $this->itemRepository->delete($item);
        });
    }
}
