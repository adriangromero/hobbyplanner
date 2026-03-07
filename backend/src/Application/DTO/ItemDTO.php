<?php

declare(strict_types=1);

namespace App\Application\DTO;

use App\Domain\Entity\Item;

final class ItemDTO
{
    public function __construct(
        public readonly string          $id,
        public readonly string          $name,
        public readonly float           $estimatedHours,
        public readonly int             $totalSessions,
        public readonly float           $totalHours,
        public readonly ?WorkSessionDTO $openSession,
    ) {}

    public static function fromEntity(
        Item             $item,
        int              $totalSessions = 0,
        float            $totalHours    = 0.0,
        ?WorkSessionDTO  $openSession   = null,
    ): self {
        return new self(
            id:             $item->id()->value(),
            name:           $item->name(),
            estimatedHours: $item->estimatedHours(),
            totalSessions:  $totalSessions,
            totalHours:     $totalHours,
            openSession:    $openSession,
        );
    }
}