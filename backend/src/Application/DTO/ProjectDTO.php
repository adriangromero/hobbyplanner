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
        public readonly string $type,
        public readonly string $createdAt,
        public readonly string $updatedAt,
        public readonly int $unitCount = 0,
        public readonly int $miniatureCount = 0,
    ) {}

    public static function fromEntity(Project $project, int $unitCount = 0, int $miniatureCount = 0): self
    {
        return new self(
            id:          $project->id()->value(),
            name:        $project->name(),
            description: $project->description(),
            status:      $project->status()->value,
            type:        $project->type()->value,
            createdAt:   $project->createdAt()->format('c'),
            updatedAt:   $project->updatedAt()->format('c'),
            unitCount:   $unitCount,
            miniatureCount: $miniatureCount,
        );
    }

    public function toArray(): array
    {
        return [
            'id'          => $this->id,
            'name'        => $this->name,
            'description' => $this->description,
            'status'      => $this->status,
            'type'        => $this->type,
            'createdAt'   => $this->createdAt,
            'updatedAt'   => $this->updatedAt,
            'unitCount' => $this->unitCount,
            'miniatureCount' => $this->miniatureCount,
        ];
    }
}
