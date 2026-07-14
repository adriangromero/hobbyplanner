<?php

declare(strict_types=1);

namespace App\Application\UseCase\Project\GetProjectWithItems;

use App\Domain\ValueObject\ItemSortField;
use App\Domain\ValueObject\ProjectId;
use App\Domain\ValueObject\SortDirection;

final class GetProjectWithItemsRequest
{
    public function __construct(
        private readonly string  $projectId,
        private readonly ?string $sortBy = null,
        private readonly ?string $direction = null,
    ) {}

    public function projectId(): ProjectId
    {
        return ProjectId::fromString($this->projectId);
    }

    public function sortBy(): ?ItemSortField
    {
        return $this->sortBy !== null ? ItemSortField::tryFrom($this->sortBy) : null;
    }

    public function direction(): SortDirection
    {
        return $this->direction !== null
            ? (SortDirection::tryFrom(strtolower($this->direction)) ?? SortDirection::ASC)
            : SortDirection::ASC;
    }
}
