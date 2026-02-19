<?php

declare(strict_types=1);

namespace App\Application\UseCase\Project\CreateProject;

final class CreateProjectRequest
{
    public function __construct(
        public readonly string $name,
        public readonly string $description
    ) {}
}