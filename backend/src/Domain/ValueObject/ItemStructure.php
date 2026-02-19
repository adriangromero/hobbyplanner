<?php

namespace App\Domain\ValueObject;

enum ItemStructure: string
{
    case SINGLE = 'SINGLE';    // Individual (1 item)
    case SET = 'SET';          // Set/Unidad (múltiples)
}