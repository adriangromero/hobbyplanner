<?php

declare(strict_types=1);

namespace App\Application\UseCase\WorkSession\DeleteWorkSession;

use App\Domain\ValueObject\WorkSessionId;

final class DeleteWorkSessionRequest
{
    public function __construct(
        private readonly string $sessionId,
    ) {}

    public function sessionId(): WorkSessionId
    {
        return WorkSessionId::fromString($this->sessionId);
    }
}