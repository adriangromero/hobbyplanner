<?php

declare(strict_types=1);

namespace App\Domain\ValueObject;

enum ItemStatus: string
{
    case PENDING     = 'pending';
    case IN_PROGRESS = 'in_progress';
    case COMPLETED   = 'completed';

    public function isCompleted(): bool
    {
        return $this === self::COMPLETED;
    }
}
