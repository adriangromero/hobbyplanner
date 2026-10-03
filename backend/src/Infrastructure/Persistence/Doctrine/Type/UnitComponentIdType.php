<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Doctrine\Type;

use App\Domain\ValueObject\UnitComponentId;

final class UnitComponentIdType extends AbstractIdType
{
    protected static function idClass(): string { return UnitComponentId::class; }
    protected static function typeName(): string { return 'unit_component_id'; }
}
