<?php

declare(strict_types=1);

namespace App\Application\UseCase\Project\GetProjectWithItems;

use App\Application\DTO\ProjectDTO;
use App\Application\DTO\ItemDTO;

final class GetProjectWithItemsResponse
{
    /** @param ItemDTO[] $items */
    public function __construct(
        private readonly ProjectDTO $project,
        private readonly array      $items,
    ) {}

    public function project(): ProjectDTO
    {
        return $this->project;
    }

    /** @return ItemDTO[] */
    public function items(): array
    {
        return $this->items;
    }
}