<?php

declare(strict_types=1);

namespace App\Application\UseCase\Item\DeleteItem;

use App\Domain\ValueObject\ItemId;

final class DeleteItemRequest
{
    public function __construct(
        private readonly string $itemId,
    ) {}

    public function itemId(): ItemId
    {
        return ItemId::fromString($this->itemId);
    }
}