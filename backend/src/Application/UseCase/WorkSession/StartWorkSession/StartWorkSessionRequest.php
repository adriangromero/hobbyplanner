<?php

declare(strict_types=1);

namespace App\Application\UseCase\WorkSession\StartWorkSession;

use App\Domain\ValueObject\ItemId;
use App\Domain\ValueObject\ProjectId;
use App\Domain\ValueObject\UserId;

final class StartWorkSessionRequest
{
    public function __construct(
        private readonly string $itemId,
        private readonly string $projectId,
        private readonly string $userId,
    ) {}

    public function itemId(): ItemId
    {
        return ItemId::fromString($this->itemId);
    }

    public function projectId(): ProjectId
    {
        return ProjectId::fromString($this->projectId);
    }

    public function userId(): UserId
    {
        return UserId::fromString($this->userId);
    }
}