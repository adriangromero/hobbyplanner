<?php

declare(strict_types=1);

namespace App\Application\UseCase\Item\ToggleItemStatus;

use App\Domain\ValueObject\ItemId;

final class ToggleItemStatusRequest
{
    public function __construct(
        private readonly string $itemId,
    ) {}

    public function itemId(): ItemId { return ItemId::fromString($this->itemId); }
}
