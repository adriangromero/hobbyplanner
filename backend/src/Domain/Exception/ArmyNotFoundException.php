<?php

declare(strict_types=1);
namespace App\Domain\Exception;
final class ArmyNotFoundException extends NotFoundException
{
    public function __construct(string $id) { parent::__construct("Ejército '{$id}' no encontrado"); }
}
