<?php

declare(strict_types=1);

namespace App\Application\UseCase\Item\GetItemSessions;

use App\Domain\ValueObject\ItemId;

final class GetItemSessionsRequest
{
    public function __construct(
        private readonly string $itemId,
    ) {}

    public function itemId(): ItemId
    {
        return ItemId::fromString($this->itemId);
    }
}