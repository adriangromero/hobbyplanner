<?php

declare(strict_types=1);

namespace App\Application\UseCase\Project\ListProjects;

use App\Application\DTO\ProjectDTO;

final class ListProjectsResponse
{
    /** @param ProjectDTO[] $projects */
    public function __construct(
        private readonly array $projects
    ) {}

    /** @return ProjectDTO[] */
    public function projects(): array
    {
        return $this->projects;
    }
}