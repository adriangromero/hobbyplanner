<?php

declare(strict_types=1);

namespace App\Application\DTO;

use App\Domain\Entity\WorkSession;

final class WorkSessionDTO
{
    public function __construct(
        public readonly string  $id,
        public readonly string  $itemId,
        public readonly string  $projectId,
        public readonly string  $startedAt,
        public readonly ?string $endedAt,
        public readonly float   $durationHours,
    ) {}

    public static function fromEntity(WorkSession $session): self
    {
        return new self(
            id:            $session->id()->value(),
            itemId:        $session->itemId()->value(),
            projectId:     $session->projectId()->value(),
            startedAt:     $session->startedAt()->format('c'),
            endedAt:       $session->endedAt()?->format('c'),
            durationHours: $session->durationHours(),
        );
    }
}