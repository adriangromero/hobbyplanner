<?php

declare(strict_types=1);

namespace App\Application\UseCase\Auth\Login;

use App\Domain\Exception\InvalidCredentialsException;
use App\Domain\Repository\UserRepositoryInterface;
use App\Domain\ValueObject\Email;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

final class LoginUseCase
{
    public function __construct(
        private readonly UserRepositoryInterface     $userRepository,
        private readonly UserPasswordHasherInterface $passwordHasher,
        private readonly JWTTokenManagerInterface    $jwtManager,
    ) {}

    public function execute(LoginRequest $request): LoginResponse
    {
        $user = $this->userRepository->findByEmail(
            Email::fromString($request->email())
        );

        if ($user === null) {
            throw new InvalidCredentialsException();
        }

        if (!$this->passwordHasher->isPasswordValid($user, $request->password())) {
            throw new InvalidCredentialsException();
        }

        $token = $this->jwtManager->create($user);

        return new LoginResponse(
            token: $token,
            id:    $user->id()->value(),
            email: $user->email()->value(),
            name:  $user->name(),
        );
    }
}