<?php

declare(strict_types=1);

namespace App\Application\DTO;

use App\Domain\Entity\UnitComponent;

final class UnitComponentDTO
{
    private function __construct(
        private string $id,
        private string $unitId,
        private string $label,
        private int $quantityTotal,
        private int $quantityPainted,
    ) {}

    public static function fromEntity(UnitComponent $component): self
    {
        return new self($component->id()->value(), $component->unitId()->value(), $component->label(), $component->quantityTotal(), $component->quantityPainted());
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'unitId' => $this->unitId,
            'label' => $this->label,
            'quantityTotal' => $this->quantityTotal,
            'quantityPainted' => $this->quantityPainted,
            'remainingQuantity' => $this->quantityTotal - $this->quantityPainted,
        ];
    }
}
