<?php

declare(strict_types=1);

namespace App\Application\UseCase\Auth\Login;

final class LoginRequest
{
    public function __construct(
        private readonly string $email,
        private readonly string $password,
    ) {}

    public function email(): string
    {
        return $this->email;
    }

    public function password(): string
    {
        return $this->password;
    }
}