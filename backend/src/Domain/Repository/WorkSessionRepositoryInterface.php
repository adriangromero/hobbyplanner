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
    
    // ✅ Cambiado de find() a findById()
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
     * @return WorkSession[]
     */
    public function findByUser(UserId $userId): array;

    public function getTotalHoursByItem(ItemId $itemId): float;
    public function getTotalHoursByProject(ProjectId $projectId): float;
    public function getTotalHoursByUser(UserId $userId): float;
    public function countByProject(ProjectId $projectId): int;
    
    /**
     * @return array<string, float>
     */
    public function getHoursByDay(ProjectId $projectId, \DateTimeImmutable $since): array;
}