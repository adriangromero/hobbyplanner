<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Doctrine\Type;

use App\Domain\ValueObject\ProjectId;

final class ProjectIdType extends AbstractIdType
{
    protected static function idClass(): string
    {
        return ProjectId::class;
    }

    protected static function typeName(): string
    {
        return 'project_id';
    }
}
