<?php

declare(strict_types=1);

namespace App\Domain\Exception;

final class WorkSessionNotFoundException extends NotFoundException
{
    public function __construct(string $id)
    {
        parent::__construct("Sesión de trabajo '{$id}' no encontrada");
    }
}