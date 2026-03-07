<?php

declare(strict_types=1);

namespace App\Domain\ValueObject;

use Ramsey\Uuid\Uuid;

final class WorkSessionId
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
        if (!Uuid::isValid($value)) {
            throw new \InvalidArgumentException("Invalid WorkSessionId: '{$value}'");
        }

        return new self($value);
    }

    public function value(): string
    {
        return $this->value;
    }

    public function equals(WorkSessionId $other): bool
    {
        return $this->value === $other->value();
    }

    public function __toString(): string
    {
        return $this->value;
    }
}
