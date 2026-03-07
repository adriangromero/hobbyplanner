<?php

declare(strict_types=1);

namespace App\Domain\Entity;

use App\Domain\Exception\ValidationException;
use App\Domain\ValueObject\Email;
use App\Domain\ValueObject\UserId;
use DateTimeImmutable;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;

final class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    private UserId            $id;
    private Email             $email;
    private string            $password;
    private string            $name;
    private array             $roles;
    private bool              $active;
    private DateTimeImmutable $createdAt;
    private DateTimeImmutable $updatedAt;

    public function __construct(
        UserId $id,
        Email  $email,
        string $password,
        string $name,
        array  $roles = ['ROLE_USER']
    ) {
        $this->id        = $id;
        $this->email     = $email;
        $this->password  = $password;
        $this->roles     = $roles;
        $this->active    = true;
        $this->createdAt = new DateTimeImmutable();
        $this->updatedAt = new DateTimeImmutable();

        $this->updateName($name);
    }

    public static function create(Email $email, string $password, string $name): self
    {
        return new self(
            UserId::create(),
            $email,
            $password,
            $name,
        );
    }

    public function id(): UserId                  { return $this->id; }
    public function email(): Email                { return $this->email; }
    public function name(): string                { return $this->name; }
    public function isActive(): bool              { return $this->active; }
    public function createdAt(): DateTimeImmutable { return $this->createdAt; }
    public function updatedAt(): DateTimeImmutable { return $this->updatedAt; }

    public function updateName(string $name): void
    {
        if (trim($name) === '') {
            throw new ValidationException('El nombre no puede estar vacío');
        }

        $this->name      = trim($name);
        $this->updatedAt = new DateTimeImmutable();
    }

    public function updatePassword(string $newPassword): void
    {
        if (trim($newPassword) === '') {
            throw new ValidationException('La contraseña no puede estar vacía');
        }

        $this->password  = $newPassword;
        $this->updatedAt = new DateTimeImmutable();
    }

    public function deactivate(): void
    {
        $this->active    = false;
        $this->updatedAt = new DateTimeImmutable();
    }

    public function activate(): void
    {
        $this->active    = true;
        $this->updatedAt = new DateTimeImmutable();
    }

    // ===== UserInterface =====

    public function getPassword(): string { return $this->password; }

    public function getRoles(): array
    {
        $roles   = $this->roles;
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    public function getUserIdentifier(): string { return $this->email->value(); }
    public function getSalt(): ?string          { return null; }
    public function eraseCredentials(): void    {}
}