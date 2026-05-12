<?php

declare(strict_types=1);

namespace App\Infrastructure\Security;

use App\Domain\Entity\User;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;

final class SecurityUser implements UserInterface, PasswordAuthenticatedUserInterface
{
    public function __construct(private readonly User $domainUser) {}

    public function domainUser(): User
    {
        return $this->domainUser;
    }

    public function getUserIdentifier(): string
    {
        return $this->domainUser->email()->value();
    }

    public function getRoles(): array
    {
        return $this->domainUser->roles();
    }

    public function getPassword(): string
    {
        return $this->domainUser->password();
    }

    public function getSalt(): ?string
    {
        return null;
    }

    public function eraseCredentials(): void {}
}
