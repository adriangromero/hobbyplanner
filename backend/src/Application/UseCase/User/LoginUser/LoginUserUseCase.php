<?php

declare(strict_types=1);

namespace App\Application\UseCase\User\LoginUser;

use App\Domain\Entity\User;
use App\Domain\Repository\UserRepositoryInterface;
use App\Domain\ValueObject\Email;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

final readonly class LoginUserUseCase
{
    public function __construct(
        private UserRepositoryInterface $userRepository,
        private UserPasswordHasherInterface $passwordHasher,
        private JWTTokenManagerInterface $jwtManager
    ) {
    }

    /**
     * @throws \InvalidArgumentException if email is invalid
     * @throws \RuntimeException if credentials are incorrect or user is disabled
     */
    public function execute(LoginUserRequest $request): LoginUserResponse
    {
        // Validate and create Email Value Object
        $email = new Email($request->email);

        // Find user by email
        $user = $this->userRepository->findByEmail($email);

        if (!$user) {
            throw new \RuntimeException('Invalid credentials');
        }

        // Verify password
        if (!$this->passwordHasher->isPasswordValid($user, $request->password)) {
            throw new \RuntimeException('Invalid credentials');
        }

        // Check if user is active
        if (!$user->isActive()) {
            throw new \RuntimeException('User is disabled');
        }

        // Generate JWT token
        $token = $this->jwtManager->create($user);

        // Return response
        return new LoginUserResponse(
            id: $user->getId()->value(),           // UserId -> string
            email: $user->getEmail()->value(),     // Email -> string
            name: $user->getName(),
            token: $token
        );
    }
}