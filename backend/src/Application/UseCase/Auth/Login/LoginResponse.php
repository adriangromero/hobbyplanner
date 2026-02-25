<?php

declare(strict_types=1);

namespace App\Application\UseCase\Auth\Login;

final class LoginResponse
{
    public function __construct(
        public readonly string $token,
        public readonly string $id,
        public readonly string $email,
        public readonly string $name,
    ) {}
}