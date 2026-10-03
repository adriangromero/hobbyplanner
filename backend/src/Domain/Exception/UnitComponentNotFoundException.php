<?php

declare(strict_types=1);

namespace App\Domain\Exception;

final class UnitComponentNotFoundException extends NotFoundException
{
    public function __construct(string $id) { parent::__construct(sprintf('Componente de unidad no encontrado: %s', $id)); }
}
