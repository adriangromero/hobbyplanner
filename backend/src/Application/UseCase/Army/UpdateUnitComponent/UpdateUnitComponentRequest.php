<?php

declare(strict_types=1);

namespace App\Application\UseCase\Army\UpdateUnitComponent;

use App\Domain\ValueObject\UnitComponentId;

final class UpdateUnitComponentRequest
{
    public function __construct(private readonly string $componentId, private readonly string $label, private readonly int $quantityTotal) {}
    public function componentId(): UnitComponentId { return UnitComponentId::fromString($this->componentId); }
    public function label(): string { return $this->label; }
    public function quantityTotal(): int { return $this->quantityTotal; }
}
