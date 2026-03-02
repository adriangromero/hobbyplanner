<?php

declare(strict_types=1);

namespace App\Application\UseCase\Project\CreateProject;

use App\Domain\ValueObject\UserId;

final class CreateProjectRequest
{
    public function __construct(
        private readonly string $userId,
        private readonly string $name,
        private readonly string $description,
    ) {}

    public function userId(): UserId { return UserId::fromString($this->userId); }
    public function name(): string   { return $this->name; }
    public function description(): string { return $this->description; }
}