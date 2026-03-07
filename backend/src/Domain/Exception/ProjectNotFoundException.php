<?php

declare(strict_types=1);

namespace App\Domain\Exception;

final class ProjectNotFoundException extends NotFoundException
{
    public function __construct(string $id)
    {
        parent::__construct("Proyecto '{$id}' no encontrado");
    }
}