<?php

declare(strict_types=1);

namespace App\Domain\ValueObject;

enum ProjectStatus: string
{
    case ACTIVE    = 'active';
    case COMPLETED = 'completed';

    public function isCompleted(): bool
    {
        return $this === self::COMPLETED;
    }
}
