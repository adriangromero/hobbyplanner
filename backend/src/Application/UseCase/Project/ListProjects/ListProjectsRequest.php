<?php

declare(strict_types=1);

namespace App\Application\UseCase\Project\ListProjects;

use App\Domain\ValueObject\UserId;

final class ListProjectsRequest
{
    public function __construct(
        private readonly string $userId,
    ) {}

    public function userId(): UserId
    {
        return UserId::fromString($this->userId);
    }
}
