<?php

declare(strict_types=1);

namespace App\Application\UseCase\Project\GetProjectWithItems;

use App\Domain\ValueObject\ProjectId;

final class GetProjectWithItemsRequest
{
    public function __construct(
        private readonly string $projectId
    ) {}

    public function projectId(): ProjectId
    {
        return ProjectId::fromString($this->projectId);
    }
}
