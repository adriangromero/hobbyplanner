<?php

declare(strict_types=1);

namespace App\Domain\ValueObject;

final class UnitComponentId extends AbstractId
{
    protected static function entityName(): string { return 'unit component'; }
}
