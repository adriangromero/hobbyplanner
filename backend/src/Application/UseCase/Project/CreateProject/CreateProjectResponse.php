<?php

declare(strict_types=1);

namespace App\Application\UseCase\Project\CreateProject;

final class CreateProjectResponse
{
    public function __construct(
        public readonly string $id,
        public readonly string $name,
        public readonly string $createdAt
    ) {}
}