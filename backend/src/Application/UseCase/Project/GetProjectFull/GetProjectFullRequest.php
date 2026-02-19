<?php

declare(strict_types=1);

namespace App\Application\UseCase\Project\GetProjectFull;

use App\Domain\ValueObject\ProjectId;

final class GetProjectFullRequest
{
    public function __construct(
        private string $projectId
    ) {}

    public function projectId(): ProjectId
    {
        return ProjectId::fromString($this->projectId);
    }
}
