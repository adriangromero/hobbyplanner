<?php

declare(strict_types=1);

namespace App\Domain\ValueObject;

enum SortDirection: string
{
    case ASC  = 'asc';
    case DESC = 'desc';
}
