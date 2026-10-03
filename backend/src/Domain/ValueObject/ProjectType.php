<?php

declare(strict_types=1);

namespace App\Domain\ValueObject;

use App\Domain\Exception\ValidationException;

enum ProjectType: string
{
    case GENERAL = 'general';
    case ARMY = 'army';

    public static function fromInput(string $type): self
    {
        return self::tryFrom($type)
            ?? throw new ValidationException('El tipo de proyecto debe ser general o ejército');
    }
}
