<?php

declare(strict_types=1);

namespace App\Domain\Repository;

use App\Domain\Entity\Project;
use App\Domain\ValueObject\ProjectId;
use App\Domain\ValueObject\UserId;

interface ProjectRepositoryInterface
{
    public function save(Project $project): void;

    public function findById(ProjectId $id): ?Project;

    /**
     * @return Project[]
     */
    public function findByUser(UserId $userId): array;

    public function delete(Project $project): void;
}
