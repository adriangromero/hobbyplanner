<?php

declare(strict_types=1);

namespace App\Application\UseCase\User\LoginUser;

final readonly class LoginUserResponse
{
    public function __construct(
        public string $id,      // UUID as string
        public string $email,
        public string $name,
        public string $token
    ) {
    }
}