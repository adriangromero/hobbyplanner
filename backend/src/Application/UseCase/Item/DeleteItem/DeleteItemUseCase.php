<?php

declare(strict_types=1);

namespace App\Application\UseCase\Item\DeleteItem;

use App\Domain\Exception\ItemNotFoundException;
use App\Domain\Repository\ItemRepositoryInterface;

final class DeleteItemUseCase
{
    public function __construct(
        private readonly ItemRepositoryInterface $itemRepository,
    ) {}

    public function execute(DeleteItemRequest $request): void
    {
        $item = $this->itemRepository->findById($request->itemId());

        if ($item === null) {
            throw new ItemNotFoundException($request->itemId()->value());
        }

        $this->itemRepository->delete($item);
    }
}