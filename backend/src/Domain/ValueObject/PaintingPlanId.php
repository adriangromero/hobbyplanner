<?php

declare(strict_types=1);

namespace App\Domain\ValueObject;

final class PaintingPlanId extends AbstractId
{
    protected static function entityName(): string { return 'painting plan'; }
}
