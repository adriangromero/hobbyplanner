<?php

declare(strict_types=1);

namespace App\Domain\Repository;

use App\Domain\Entity\WorkSession;
use App\Domain\ValueObject\WorkSessionId;
use App\Domain\ValueObject\ItemId;
use App\Domain\ValueObject\ProjectId;
use App\Domain\ValueObject\UserId;

interface WorkSessionRepositoryInterface
{
    public function save(WorkSession $session): void;

    public function findById(WorkSessionId $id): ?WorkSession;

    public function delete(WorkSession $session): void;

    /**
     * @return WorkSession[]
     */
    public function findByItem(ItemId $itemId): array;

    /**
     * @return WorkSession[]
     */
    public function findByProject(ProjectId $projectId): array;

    /**
     * @return array<string, WorkSession[]> itemId => WorkSession[]
     */
    public function findGroupedByItem(ProjectId $projectId): array;

    public function findActiveByUser(UserId $userId): ?WorkSession;

    public function deleteByItem(ItemId $itemId): void;

    public function deleteByProject(ProjectId $projectId): void;
}