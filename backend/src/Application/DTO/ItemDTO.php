<?php

declare(strict_types=1);

namespace App\Application\DTO;

use App\Domain\Entity\Item;

final class ItemDTO
{
    /**
     * @param WorkSessionDTO[] $sessions
     */
    public function __construct(
        public readonly string $id,
        public readonly string $name,
        public readonly float  $estimatedHours,
        public readonly int    $totalSessions,
        public readonly float  $totalHours,
        public readonly array  $sessions,
    ) {}

    /**
     * @param WorkSessionDTO[] $sessions
     */
    public static function fromEntity(Item $item, int $totalSessions = 0, array $sessions = []): self
    {
        return new self(
            id:             $item->id()->value(),
            name:           $item->name(),
            estimatedHours: $item->estimatedHours(),
            totalSessions:  $totalSessions,
            totalHours:     array_reduce(
                $sessions,
                fn(float $carry, WorkSessionDTO $s) => $carry + $s->durationHours,
                0.0
            ),
            sessions:       $sessions,
        );
    }
}