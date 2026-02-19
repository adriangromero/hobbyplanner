<?php

declare(strict_types=1);

namespace App\Application\UseCase\User\CreateUser;

final class CreateUserRequest
{
    public function __construct(
        public readonly string $email,
        public readonly string $password,
        public readonly string $name
    ) {}
}