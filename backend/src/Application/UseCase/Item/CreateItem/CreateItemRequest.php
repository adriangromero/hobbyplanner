<?php

declare(strict_types=1);

namespace App\Application\UseCase\Item\CreateItem;

use App\Domain\ValueObject\ProjectId;
use App\Domain\ValueObject\UserId;

final class CreateItemRequest
{
    public function __construct(
        private readonly string $projectId,
        private readonly string $userId,
        private readonly string $name,
        private readonly float  $estimatedHours,
    ) {}

    public function projectId(): ProjectId      { return ProjectId::fromString($this->projectId); }
    public function userId(): UserId            { return UserId::fromString($this->userId); }
    public function name(): string              { return $this->name; }
    public function estimatedHours(): float     { return $this->estimatedHours; }
}