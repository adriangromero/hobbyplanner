<?php

declare(strict_types=1);

namespace App\Application\UseCase\User\CreateUser;

use App\Domain\Entity\User;
use App\Domain\Exception\UserAlreadyExistsException;
use App\Domain\Repository\UserRepositoryInterface;
use App\Domain\ValueObject\Email;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

final class CreateUserUseCase
{
    public function __construct(
        private UserRepositoryInterface $userRepository,
        private UserPasswordHasherInterface $passwordHasher
    ) {}

    public function execute(CreateUserRequest $request): CreateUserResponse
    {
        $email = new Email($request->email);

        // Verificar que no exista
        if ($this->userRepository->emailExists($email)) {
            throw new UserAlreadyExistsException($email);
        }

        // Crear usuario
        $user = User::create(
            $email,
            $request->password, // Se hasheará después
            $request->name
        );

        // Hashear password (necesitamos la entidad para Symfony)
        $hashedPassword = $this->passwordHasher->hashPassword(
            $user,
            $request->password
        );
        $user->updatePassword($hashedPassword);

        // Guardar
        $this->userRepository->save($user);

        // Retornar response
        return new CreateUserResponse(
            $user->id()->value(),
            $user->email()->value(),
            $user->name(),
            $user->createdAt()->format('Y-m-d H:i:s')
        );
    }
}