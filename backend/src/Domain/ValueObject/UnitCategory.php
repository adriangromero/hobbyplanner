<?php

declare(strict_types=1);

namespace App\Domain\ValueObject;

use App\Domain\Exception\ValidationException;

enum UnitCategory: string
{
    case CHARACTER = 'character';
    case HERO = 'hero';
    case INFANTRY = 'infantry';
    case SPECIAL = 'special';
    case SINGULAR = 'singular';
    case WAR_MACHINE = 'war_machine';

    public static function fromValue(string $value): self
    {
        return self::tryFrom($value) ?? throw new ValidationException('La categoría de unidad no es válida');
    }
}
