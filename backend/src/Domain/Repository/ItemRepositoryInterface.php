<?php

declare(strict_types=1);

namespace App\Domain\Repository;

use App\Domain\Entity\Item;
use App\Domain\ValueObject\ItemId;
use App\Domain\ValueObject\ItemSortField;
use App\Domain\ValueObject\ProjectId;
use App\Domain\ValueObject\SortDirection;
use App\Domain\ValueObject\UserId;

interface ItemRepositoryInterface
{
    public function save(Item $item): void;
    public function findById(ItemId $id): ?Item;

    /**
     * @return Item[]
     */
    public function findByProject(
        ProjectId      $projectId,
        ?ItemSortField $sortBy = null,
        SortDirection  $direction = SortDirection::ASC,
    ): array;

    /**
     * @return Item[]
     */
    public function findByUser(UserId $userId): array;

    public function getTotalEstimatedHoursByProject(ProjectId $projectId): float;
    public function countByProject(ProjectId $projectId): int;
    public function delete(Item $item): void;

    public function deleteByProject(ProjectId $projectId): void;
}
