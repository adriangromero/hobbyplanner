<?php

declare(strict_types=1);

namespace App\Application\UseCase\Inventory;

use App\Application\DTO\ItemDTO;

final class ListInventoryResponse
{
    /**
     * @param ItemDTO[] $items
     */
    public function __construct(
        private readonly array $items,
    ) {}

    /** @return ItemDTO[] */
    public function items(): array { return $this->items; }
}
