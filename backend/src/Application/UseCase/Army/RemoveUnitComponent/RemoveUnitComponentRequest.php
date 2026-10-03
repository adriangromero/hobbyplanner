<?php

declare(strict_types=1);

namespace App\Application\UseCase\Army\RemoveUnitComponent;

use App\Domain\ValueObject\UnitComponentId;

final class RemoveUnitComponentRequest
{
    public function __construct(private readonly string $componentId) {}
    public function componentId(): UnitComponentId { return UnitComponentId::fromString($this->componentId); }
}
