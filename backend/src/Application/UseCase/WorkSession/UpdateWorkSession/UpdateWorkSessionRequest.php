<?php

declare(strict_types=1);

namespace App\Application\UseCase\WorkSession\UpdateWorkSession;

use App\Domain\ValueObject\WorkSessionId;
use DateTimeImmutable;

final class UpdateWorkSessionRequest
{
    public function __construct(
        private readonly string  $sessionId,
        private readonly string  $startedAt,
        private readonly ?string $endedAt,
    ) {}

    public function sessionId(): WorkSessionId
    {
        return WorkSessionId::fromString($this->sessionId);
    }

    public function startedAt(): DateTimeImmutable
    {
        return new DateTimeImmutable($this->startedAt);
    }

    public function endedAt(): ?DateTimeImmutable
    {
        return $this->endedAt ? new DateTimeImmutable($this->endedAt) : null;
    }
}