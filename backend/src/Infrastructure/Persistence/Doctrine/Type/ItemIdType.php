<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Doctrine\Type;

use App\Domain\ValueObject\ItemId;

final class ItemIdType extends AbstractIdType
{
    protected static function idClass(): string
    {
        return ItemId::class;
    }

    protected static function typeName(): string
    {
        return 'item_id';
    }
}
