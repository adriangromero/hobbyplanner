<?php

declare(strict_types=1);

namespace App\Infrastructure\Security;

use App\Application\Port\PasswordHasherPort;
use App\Domain\Entity\User;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

final class SymfonyPasswordHasher implements PasswordHasherPort
{
    public function __construct(
        private readonly UserPasswordHasherInterface $hasher,
    ) {}

    public function hash(User $user, string $plainPassword): string
    {
        return $this->hasher->hashPassword(new SecurityUser($user), $plainPassword);
    }

    public function verify(User $user, string $plainPassword): bool
    {
        return $this->hasher->isPasswordValid(new SecurityUser($user), $plainPassword);
    }
}
