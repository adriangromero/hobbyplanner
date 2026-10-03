<?php

declare(strict_types=1);

namespace App\Application\UseCase\Army\UpdateUnitComponentProgress;

use App\Domain\ValueObject\UnitComponentId;

final class UpdateUnitComponentProgressRequest
{
    public function __construct(private readonly string $componentId, private readonly int $delta) {}
    public function componentId(): UnitComponentId { return UnitComponentId::fromString($this->componentId); }
    public function delta(): int { return $this->delta; }
}
