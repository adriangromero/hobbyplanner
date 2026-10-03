<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Doctrine\Type;

use App\Domain\ValueObject\PaintingPlanId;

final class PaintingPlanIdType extends AbstractIdType
{
    protected static function idClass(): string { return PaintingPlanId::class; }
    protected static function typeName(): string { return 'painting_plan_id'; }
}
