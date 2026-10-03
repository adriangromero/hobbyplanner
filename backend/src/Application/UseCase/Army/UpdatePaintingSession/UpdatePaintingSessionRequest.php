<?php

declare(strict_types=1);

namespace App\Application\UseCase\Army\UpdatePaintingSession;

use App\Domain\ValueObject\PaintingSessionId;

final class UpdatePaintingSessionRequest
{
    public function __construct(
        private readonly string $sessionId,
        private readonly int $durationSeconds,
    ) {}

    public function sessionId(): PaintingSessionId { return PaintingSessionId::fromString($this->sessionId); }
    public function durationSeconds(): int { return $this->durationSeconds; }
}
