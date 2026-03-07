<?php

declare(strict_types=1);

namespace App\Domain\ValueObject;

use App\Domain\Exception\InvalidEmailException;

/**
 * Value Object for Email.
 * 
 * Why not use string directly?
 * - Strong typing: function findUser(Email $email) vs function findUser(string $email)
 * - You can't pass a name where an email goes by mistake
 * - Email validates itself
 */
final class Email
{
    private string $value;

    public function __construct(string $value)
    {
        $this->validate($value);
        $this->value = strtolower(trim($value));
    }

    private function validate(string $email): void
    {
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new InvalidEmailException($email);
        }
    }

    public function value(): string
    {
        return $this->value;
    }

    public static function fromString(string $value): self
    {
        return new self($value);
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