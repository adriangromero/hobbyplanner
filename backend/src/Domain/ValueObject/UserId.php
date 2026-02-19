<?php

declare(strict_types=1);

namespace App\Domain\ValueObject;

use Ramsey\Uuid\Uuid;

/**
 * Value Object para el ID de Usuario.
 * 
 * ¿Por qué no usar string directamente?
 * - Tipado fuerte: function findUser(UserId $id) vs function findUser(string $id)
 * - No puedes pasar un email donde va un ID por error
 * - El ID sabe generarse solo (UUID)
 */

final class UserId
{
    private string $value;

    private function __construct(string $value)
    {
        $this->value = $value;
    }

    public static function create(): self
    {
        return new self(Uuid::uuid4()->toString());
    }

    public static function fromString(string $value): self
    {
        return new self($value);
    }

    public function value(): string
    {
        return $this->value;
    }

    public function equals(UserId $other): bool
    {
        return $this->value === $other->value();
    }

    public function __toString(): string
    {
        return $this->value;
    }
}
