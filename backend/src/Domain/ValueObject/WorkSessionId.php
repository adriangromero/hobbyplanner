<?php

declare(strict_types=1);

namespace App\Domain\ValueObject;

final class WorkSessionId extends AbstractId
{
    protected static function entityName(): string
    {
        return 'sesión';
    }
}
