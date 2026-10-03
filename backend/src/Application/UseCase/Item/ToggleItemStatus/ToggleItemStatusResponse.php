<?php

declare(strict_types=1);

namespace App\Application\UseCase\Item\ToggleItemStatus;

use App\Application\DTO\ItemDTO;

final class ToggleItemStatusResponse
{
    public function __construct(
        private readonly ItemDTO $item,
    ) {}

    public function item(): ItemDTO { return $this->item; }
}
