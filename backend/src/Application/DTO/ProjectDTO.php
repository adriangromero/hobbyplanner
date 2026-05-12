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
        public readonly string $status,
        public readonly string $createdAt,
        public readonly string $updatedAt,
    ) {}

    public static function fromEntity(Project $project): self
    {
        return new self(
            id:          $project->id()->value(),
            name:        $project->name(),
            description: $project->description(),
            status:      $project->status()->value,
            createdAt:   $project->createdAt()->format('c'),
            updatedAt:   $project->updatedAt()->format('c'),
        );
    }

    public function toArray(): array
    {
        return [
            'id'          => $this->id,
            'name'        => $this->name,
            'description' => $this->description,
            'status'      => $this->status,
            'createdAt'   => $this->createdAt,
            'updatedAt'   => $this->updatedAt,
        ];
    }
}
