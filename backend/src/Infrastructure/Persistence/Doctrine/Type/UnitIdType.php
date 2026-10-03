<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Doctrine\Type;

use App\Domain\ValueObject\UnitId;

final class UnitIdType extends AbstractIdType
{
    protected static function idClass(): string { return UnitId::class; }
    protected static function typeName(): string { return 'unit_id'; }
}
