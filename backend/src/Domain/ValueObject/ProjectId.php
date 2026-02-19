<?php

declare(strict_types=1);

namespace App\Domain\ValueObject;

use Ramsey\Uuid\Uuid;

/**
 * Value Object para el ID de Proyecto.
 *
 * Ventajas:
 * - Tipado fuerte: evita pasar strings incorrectos
 * - Generación automática de UUID
 * - Comparación segura entre IDs
 * - Compatible con Doctrine mediante un Custom Type
 */

final class ProjectId
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

    public function equals(ProjectId $other): bool
    {
        return $this->value === $other->value();
    }

    public function __toString(): string
    {
        return $this->value;
    }
}
