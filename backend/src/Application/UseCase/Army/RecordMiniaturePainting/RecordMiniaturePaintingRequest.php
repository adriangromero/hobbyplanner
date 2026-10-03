<?php

declare(strict_types=1);

namespace App\Application\UseCase\Army\RecordMiniaturePainting;

use App\Domain\ValueObject\UnitComponentId;
use App\Domain\ValueObject\UserId;

final class RecordMiniaturePaintingRequest
{
    public function __construct(
        private readonly string $componentId,
        private readonly string $userId,
        private readonly int $durationSeconds,
    ) {}

    public function componentId(): UnitComponentId { return UnitComponentId::fromString($this->componentId); }
    public function userId(): UserId { return UserId::fromString($this->userId); }
    public function durationSeconds(): int { return $this->durationSeconds; }
}
