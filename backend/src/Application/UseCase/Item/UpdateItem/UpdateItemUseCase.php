<?php

declare(strict_types=1);

namespace App\Application\UseCase\Item\UpdateItem;

use App\Application\DTO\ItemDTO;
use App\Domain\Exception\ItemNotFoundException;
use App\Domain\Repository\ItemRepositoryInterface;

final class UpdateItemUseCase
{
    public function __construct(
        private readonly ItemRepositoryInterface $itemRepository,
    ) {}

    public function execute(UpdateItemRequest $request): UpdateItemResponse
    {
        $item = $this->itemRepository->findById($request->itemId());

        if ($item === null) {
            throw new ItemNotFoundException($request->itemId()->value());
        }

        $item->update($request->name(), $request->estimatedHours());

        $this->itemRepository->save($item);

        return new UpdateItemResponse(
            ItemDTO::fromEntity($item)
        );
    }
}