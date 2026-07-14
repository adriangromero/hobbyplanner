<?php

declare(strict_types=1);

namespace App\Domain\ValueObject;

enum ItemSortField: string
{
    case NAME            = 'name';
    case ESTIMATED_HOURS = 'estimatedHours';
    case STATUS          = 'status';
    case CREATED_AT      = 'createdAt';
}
