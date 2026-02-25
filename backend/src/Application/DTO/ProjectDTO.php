<?php

declare(strict_types=1);

namespace App\Application\DTO;

use App\Domain\Entity\Project;

final class ProjectDTO
{
    public function __construct(
        public readonly string $id,
        public readonly string $name,
        public readonly string $description,
        public readonly string $createdAt,
        public readonly string $updatedAt,
    ) {}

    public static function fromEntity(Project $project): self
    {
        return new self(
            id:          $project->id()->value(),
            name:        $project->name(),
            description: $project->description(),
            createdAt:   $project->createdAt()->format('c'),
            updatedAt:   $project->updatedAt()->format('c'),
        );
    }
}