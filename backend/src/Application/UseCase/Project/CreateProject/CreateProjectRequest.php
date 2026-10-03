<?php

declare(strict_types=1);

namespace App\Application\UseCase\Project\CreateProject;

use App\Domain\ValueObject\UserId;
use App\Domain\ValueObject\ProjectType;

final class CreateProjectRequest
{
    public function __construct(
        private readonly string $userId,
        private readonly string $name,
        private readonly string $description,
        private readonly string $type = 'general',
    ) {}

    public function userId(): UserId { return UserId::fromString($this->userId); }
    public function name(): string   { return $this->name; }
    public function description(): string { return $this->description; }
    public function type(): ProjectType { return ProjectType::fromInput($this->type); }
}
