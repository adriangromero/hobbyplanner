<?php

declare(strict_types=1);

namespace App\Application\UseCase\Item\CreateItem;

use App\Application\DTO\ItemDTO;

final class CreateItemResponse
{
    /** @param ItemDTO[] $items */
    public function __construct(
        private readonly ItemDTO $item,
    ) {}

    public function item(): ItemDTO
    {
        return $this->item;
    }
}