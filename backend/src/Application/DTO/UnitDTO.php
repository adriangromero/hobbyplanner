<?php

declare(strict_types=1);

namespace App\Application\DTO;

use App\Domain\Entity\Unit;
use App\Domain\Entity\UnitComponent;

final class UnitDTO
{
    private function __construct(
        private string $id,
        private string $projectId,
        private string $name,
        private string $category,
        private int $position,
        private array $components,
        private ?array $paintingPlan,
    ) {}

    public static function fromEntity(Unit $unit, array $components = [], ?array $paintingPlan = null): self
    {
        return new self(
            $unit->id()->value(),
            $unit->projectId()->value(),
            $unit->name(),
            $unit->category()->value,
            $unit->position(),
            array_map(static fn(UnitComponent $component): array => UnitComponentDTO::fromEntity($component)->toArray(), $components),
            $paintingPlan,
        );
    }

    public function toArray(): array
    {
        return ['id' => $this->id, 'projectId' => $this->projectId, 'name' => $this->name, 'category' => $this->category, 'position' => $this->position, 'components' => $this->components, 'paintingPlan' => $this->paintingPlan];
    }
}
