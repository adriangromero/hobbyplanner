<?php

declare(strict_types=1);

namespace App\Application\UseCase\Army\CompleteUnit;

use App\Domain\ValueObject\UnitId;

final class CompleteUnitRequest
{
    public function __construct(private readonly string $unitId) {}
    public function unitId(): UnitId { return UnitId::fromString($this->unitId); }
}
