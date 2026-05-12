<?php

declare(strict_types=1);

namespace App\Application\UseCase\Auth\Login;

use App\Application\Port\PasswordHasherPort;
use App\Application\Port\TokenGeneratorPort;
use App\Domain\Exception\InvalidCredentialsException;
use App\Domain\Repository\UserRepositoryInterface;
use App\Domain\ValueObject\Email;

final class LoginUseCase
{
    public function __construct(
        private readonly UserRepositoryInterface $userRepository,
        private readonly PasswordHasherPort      $passwordHasher,
        private readonly TokenGeneratorPort      $tokenGenerator,
    ) {}

    public function execute(LoginRequest $request): LoginResponse
    {
        $user = $this->userRepository->findByEmail(
            Email::fromString($request->email())
        );

        if ($user === null) {
            throw new InvalidCredentialsException();
        }

        if (!$this->passwordHasher->verify($user, $request->password())) {
            throw new InvalidCredentialsException();
        }

        $token = $this->tokenGenerator->generate($user);

        return new LoginResponse(
            token: $token,
            id:    $user->id()->value(),
            email: $user->email()->value(),
            name:  $user->name(),
        );
    }
}
