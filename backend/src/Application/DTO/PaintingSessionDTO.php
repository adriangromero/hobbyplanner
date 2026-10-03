<?php

declare(strict_types=1);

namespace App\Application\DTO;

use App\Domain\Entity\PaintingSession;

final class PaintingSessionDTO
{
    private function __construct(private string $id, private string $paintingPlanId, private int $durationSeconds, private float $durationHours, private string $workedAt) {}
    public static function fromEntity(PaintingSession $session): self
    {
        return new self($session->id()->value(), $session->paintingPlanId()->value(), $session->durationSeconds(), $session->durationHours(), $session->workedAt()->format(DATE_ATOM));
    }
    public function toArray(): array
    {
        return ['id' => $this->id, 'paintingPlanId' => $this->paintingPlanId, 'durationSeconds' => $this->durationSeconds, 'durationHours' => $this->durationHours, 'workedAt' => $this->workedAt];
    }
}
