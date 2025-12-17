<?php

declare(strict_types=1);

namespace App\Application\UseCase\User\CreateUser;

final class CreateUserResponse
{
    public function __construct(
        public readonly string $id,
        public readonly string $email,
        public readonly string $name,
        public readonly string $createdAt
    ) {}
}