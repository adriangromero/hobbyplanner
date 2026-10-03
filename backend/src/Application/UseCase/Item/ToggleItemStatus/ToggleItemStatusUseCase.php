<?php

declare(strict_types=1);

namespace App\Application\UseCase\Item\ToggleItemStatus;

use App\Application\DTO\ItemDTO;
use App\Application\Security\OwnershipGuard;
use App\Domain\Exception\ItemNotFoundException;
use App\Domain\Repository\ItemRepositoryInterface;

final class ToggleItemStatusUseCase
{
    public function __construct(
        private readonly ItemRepositoryInterface $itemRepository,
        private readonly OwnershipGuard          $ownershipGuard,
    ) {}

    public function execute(ToggleItemStatusRequest $request): ToggleItemStatusResponse
    {
        $item = $this->itemRepository->findById($request->itemId());

        if ($item === null) {
            throw new ItemNotFoundException($request->itemId()->value());
        }

        $this->ownershipGuard->ensureOwnership($item);

        $item->advanceStatus();

        $this->itemRepository->save($item);

        return new ToggleItemStatusResponse(
            ItemDTO::fromEntity($item)
        );
    }
}
