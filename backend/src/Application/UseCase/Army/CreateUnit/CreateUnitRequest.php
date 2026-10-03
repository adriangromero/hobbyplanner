<?php

declare(strict_types=1);

namespace App\Application\UseCase\Army\CreateUnit;

use App\Domain\ValueObject\ProjectId;
use App\Domain\ValueObject\UserId;

final class CreateUnitRequest
{
    public function __construct(
        private readonly string $projectId,
        private readonly string $userId,
        private readonly string $name,
        private readonly string $category = 'infantry',
        private readonly int $modelsPerRow = 5,
    ) {}
    public function projectId(): ProjectId { return ProjectId::fromString($this->projectId); }
    public function userId(): UserId { return UserId::fromString($this->userId); }
    public function name(): string { return $this->name; }
    public function category(): string { return $this->category; }
    public function modelsPerRow(): int { return $this->modelsPerRow; }
}
