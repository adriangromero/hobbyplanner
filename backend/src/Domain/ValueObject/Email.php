<?php

declare(strict_types=1);

namespace App\Domain\ValueObject;

use App\Domain\Exception\InvalidEmailException;

/**
 * Value Object para Email.
 * 
 * SOLID aplicado:
 * - Single Responsibility: Solo valida y guarda un email
 * - Open/Closed: Puedes extenderlo, no modificarlo
 * 
 * Características de un Value Object:
 * - Inmutable (no tiene setters)
 * - Se compara por valor, no por referencia
 * - Se valida en construcción
 */
final class Email
{
    private string $value;

    public function __construct(string $value)
    {
        $this->ensureIsValid($value);
        $this->value = $value;
    }

    private function ensureIsValid(string $value): void
    {
        if (!filter_var($value, FILTER_VALIDATE_EMAIL)) {
            throw new InvalidEmailException($value);
        }
    }

    public function value(): string
    {
        return $this->value;
    }

    public function equals(Email $other): bool
    {
        return $this->value === $other->value();
    }

    public function __toString(): string
    {
        return $this->value;
    }
}