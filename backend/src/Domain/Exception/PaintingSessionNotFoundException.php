<?php

declare(strict_types=1);

namespace App\Domain\Exception;

final class PaintingSessionNotFoundException extends NotFoundException
{
    public function __construct(string $id)
    {
        parent::__construct("Sesión de pintado '{$id}' no encontrada");
    }
}
