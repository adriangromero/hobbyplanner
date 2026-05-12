<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Doctrine\Type;

use App\Domain\ValueObject\UserId;

final class UserIdType extends AbstractIdType
{
    protected static function idClass(): string
    {
        return UserId::class;
    }

    protected static function typeName(): string
    {
        return 'user_id';
    }
}
