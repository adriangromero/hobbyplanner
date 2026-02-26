<?php

declare(strict_types=1);

namespace App\Application\UseCase\WorkSession\UpdateWorkSession;

use App\Application\DTO\WorkSessionDTO;

final class UpdateWorkSessionResponse
{
    public function __construct(
        private readonly WorkSessionDTO $session,
    ) {}

    public function session(): WorkSessionDTO
    {
        return $this->session;
    }
}