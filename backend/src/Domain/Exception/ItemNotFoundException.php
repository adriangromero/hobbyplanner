<?php

declare(strict_types=1);

namespace App\Domain\Exception;

final class ItemNotFoundException extends NotFoundException
{
    public function __construct(string $id)
    {
        parent::__construct("Item '{$id}' no encontrado");
    }
}