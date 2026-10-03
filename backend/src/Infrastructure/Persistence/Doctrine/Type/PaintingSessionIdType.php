<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Doctrine\Type;

use App\Domain\ValueObject\PaintingSessionId;

final class PaintingSessionIdType extends AbstractIdType
{
    protected static function idClass(): string { return PaintingSessionId::class; }
    protected static function typeName(): string { return 'painting_session_id'; }
}
