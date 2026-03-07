<?php

declare(strict_types=1);

namespace App\Application\UseCase\Project\UpdateProject;

use App\Domain\ValueObject\ProjectId;

final class UpdateProjectRequest
{
    public function __construct(
        private readonly string $projectId,
        private readonly string $name,
        private readonly string $description,
    ) {}

    public function projectId(): ProjectId
    {
        return ProjectId::fromString($this->projectId);
    }

    public function name(): string
    {
        return $this->name;
    }

    public function description(): string
    {
        return $this->description;
    }
}
