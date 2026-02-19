<?php

namespace App\Application\UseCase\Project\GetProjectWithStats;

use App\Domain\ValueObject\ProjectStats;

final readonly class GetProjectWithStatsResponse
{
    public function __construct(
        public string $id,
        public string $name,
        public ?string $description,
        public string $createdAt,
        public ProjectStats $stats  // ⭐ Stats como Value Object
    ) {}
}