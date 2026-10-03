<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Doctrine\Type;

use App\Domain\ValueObject\ProjectType;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\Type;

final class ProjectTypeType extends Type
{
    public const NAME = 'project_type';

    public function convertToPHPValue(mixed $value, AbstractPlatform $platform): mixed
    {
        return $value === null ? null : ProjectType::from((string) $value);
    }

    public function convertToDatabaseValue(mixed $value, AbstractPlatform $platform): mixed
    {
        return $value instanceof ProjectType ? $value->value : $value;
    }

    public function getSQLDeclaration(array $fieldDeclaration, AbstractPlatform $platform): string
    {
        return $platform->getStringTypeDeclarationSQL($fieldDeclaration);
    }

    public function getName(): string
    {
        return self::NAME;
    }

    public function requiresSQLCommentHint(AbstractPlatform $platform): bool
    {
        return true;
    }
}
