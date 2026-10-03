<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Doctrine\Type;

use App\Domain\ValueObject\ArmyId;

final class ArmyIdType extends AbstractIdType
{
    protected static function idClass(): string { return ArmyId::class; }
    protected static function typeName(): string { return 'army_id'; }
}
