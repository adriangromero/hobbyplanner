<?php

declare(strict_types=1);

namespace App\Domain\Entity;

use App\Domain\ValueObject\UserId;
use App\Domain\ValueObject\Email;
use DateTimeImmutable;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;

final class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    private UserId $id;
    private Email $email;
    private string $password;
    private string $name;
    private DateTimeImmutable $createdAt;
    private ?DateTimeImmutable $updatedAt;

    public function __construct(
        UserId $id,
        Email $email,
        string $password,
        string $name
    ) {
        $this->id = $id;
        $this->email = $email;
        $this->password = $password;
        $this->name = $name;
        $this->createdAt = new DateTimeImmutable();
        $this->updatedAt = null;
    }

    public static function create(
        Email $email,
        string $password,
        string $name
    ): self {
        return new self(
            new UserId(),
            $email,
            $password,
            $name
        );
    }

    // Getters
    public function id(): UserId
    {
        return $this->id;
    }

    public function email(): Email
    {
        return $this->email;
    }

    public function password(): string
    {
        return $this->password;
    }

    public function name(): string
    {
        return $this->name;
    }

    public function createdAt(): DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function updatedAt(): ?DateTimeImmutable
    {
        return $this->updatedAt;
    }

    // Métodos de UserInterface (requeridos por Symfony Security)
    public function getRoles(): array
    {
        return ['ROLE_USER'];
    }

    public function eraseCredentials(): void
    {
        // Si guardas datos sensibles temporales, límpialos aquí
    }

    public function getUserIdentifier(): string
    {
        return $this->email->value();
    }

    // PasswordAuthenticatedUserInterface
    public function getPassword(): string
    {
        return $this->password;
    }

    // Comportamiento
    public function updateName(string $name): void
    {
        $this->name = $name;
        $this->updatedAt = new DateTimeImmutable();
    }

    public function updatePassword(string $hashedPassword): void
    {
        $this->password = $hashedPassword;
        $this->updatedAt = new DateTimeImmutable();
    }
}