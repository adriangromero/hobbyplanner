<?php

namespace App\Domain\ValueObject;

enum ItemStatus: string
{
    case PENDING = 'PENDING';    // Individual (1 item)
    case IN_PROGRESS = 'IN_PROGRESS';    // Individual (1 item)
    case COMPLETED = 'COMPLETED';          // Set/Unidad (múltiples)
}