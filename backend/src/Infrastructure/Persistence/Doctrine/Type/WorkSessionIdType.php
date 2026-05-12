<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Doctrine\Type;

use App\Domain\ValueObject\WorkSessionId;

final class WorkSessionIdType extends AbstractIdType
{
    protected static function idClass(): string
    {
        return WorkSessionId::class;
    }

    protected static function typeName(): string
    {
        return 'work_session_id';
    }
}
