<?php

declare(strict_types=1);

namespace App\Application\UseCase\Project\CreateProject;

use App\Application\DTO\ProjectDTO;

final class CreateProjectResponse
{
    /** @param ProjectDTO[] $projects */
    public function __construct(
        private readonly ProjectDTO $project,
    ) {}

    public function project(): ProjectDTO
    {
        return $this->project;
    }
}