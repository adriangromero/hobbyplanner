<?php

declare(strict_types=1);

namespace App\Application\UseCase\User\LoginUser;

final class LoginUserRequest
{
    public function __construct(
        public string $email,
        public string $password
    ) {}
}
