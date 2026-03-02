<?php

declare(strict_types=1);

namespace App\Application\UseCase\Item\UpdateItem;

use App\Application\DTO\ItemDTO;

final class UpdateItemResponse
{
    public function __construct(
        private readonly ItemDTO $item,
    ) {}

    public function item(): ItemDTO
    {
        return $this->item;
    }
}