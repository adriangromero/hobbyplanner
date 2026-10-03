<?php

declare(strict_types=1);

namespace App\Application\UseCase\Army\UpdateUnitFormation;

use App\Domain\ValueObject\UnitId;

final class UpdateUnitFormationRequest
{
    public function __construct(private readonly string $unitId, private readonly int $modelsPerRow) {}
    public function unitId(): UnitId { return UnitId::fromString($this->unitId); }
    public function modelsPerRow(): int { return $this->modelsPerRow; }
}
