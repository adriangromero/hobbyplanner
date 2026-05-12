<?php

declare(strict_types=1);

namespace App\Domain\ValueObject;

use App\Domain\Exception\ValidationException;
use Ramsey\Uuid\Uuid;

abstract class AbstractId
{
    protected function __construct(private readonly string $value) {}

    public static function create(): static
    {
        return new static(Uuid::uuid4()->toString());
    }

    public static function fromString(string $value): static
    {
        if (!Uuid::isValid($value)) {
            throw new ValidationException(
                sprintf("ID de %s no válido: '%s'", static::entityName(), $value)
            );
        }

        return new static($value);
    }

    public function value(): string
    {
        return $this->value;
    }

    public function equals(self $other): bool
    {
        return $this->value === $other->value;
    }

    public function __toString(): string
    {
        return $this->value;
    }

    abstract protected static function entityName(): string;
}
