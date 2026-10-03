<?php

declare(strict_types=1);

namespace App\Domain\Exception;

final class UnitNotFoundException extends NotFoundException
{
    public function __construct(string $id) { parent::__construct(sprintf('Unidad no encontrada: %s', $id)); }
}
