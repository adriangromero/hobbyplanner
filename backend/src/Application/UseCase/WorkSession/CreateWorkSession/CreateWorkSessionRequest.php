<?php

declare(strict_types=1);

namespace App\Application\UseCase\WorkSession\CreateWorkSession;

final class CreateWorkSessionRequest
{
    public function __construct(
        public readonly string $name,
        public readonly string $description
    ) {}
}