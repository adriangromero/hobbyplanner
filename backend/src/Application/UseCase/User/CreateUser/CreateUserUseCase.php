<?php

declare(strict_types=1);

namespace App\Application\UseCase\User\CreateUser;

use App\Application\Port\PasswordHasherPort;
use App\Domain\Entity\User;
use App\Domain\Exception\UserAlreadyExistsException;
use App\Domain\Repository\UserRepositoryInterface;
use App\Domain\ValueObject\Email;

final class CreateUserUseCase
{
    public function __construct(
        private readonly UserRepositoryInterface $userRepository,
        private readonly PasswordHasherPort      $passwordHasher,
    ) {}

    public function execute(CreateUserRequest $request): CreateUserResponse
    {
        $email = Email::fromString($request->email);

        if ($this->userRepository->emailExists($email)) {
            throw new UserAlreadyExistsException($email);
        }

        $user = User::create($email, $request->password, $request->name);

        $hashedPassword = $this->passwordHasher->hash($user, $request->password);
        $user->updatePassword($hashedPassword);

        $this->userRepository->save($user);

        return new CreateUserResponse(
            $user->id()->value(),
            $user->email()->value(),
            $user->name(),
            $user->createdAt()->format('c'),
        );
    }
}
