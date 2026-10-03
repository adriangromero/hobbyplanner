<?php

declare(strict_types=1);
namespace App\Domain\Exception;
final class PaintingPlanNotFoundException extends NotFoundException
{
    public function __construct(string $id) { parent::__construct("Plan de pintado '{$id}' no encontrado"); }
}
