<?php

declare(strict_types=1);

namespace App\Domain\Repository;

use App\Domain\Entity\Unit;
use App\Domain\ValueObject\ProjectId;
use App\Domain\ValueObject\UnitId;

interface UnitRepositoryInterface
{
    public function save(Unit $unit): void;
    public function findById(UnitId $id): ?Unit;
    /** @return Unit[] */
    public function findByProject(ProjectId $projectId): array;
    /**
     * @param ProjectId[] $projectIds
     * @return array<string, array{unitCount: int, miniatureCount: int}>
     */
    public function inventorySummaryByProjects(array $projectIds): array;
}
