<?php

declare(strict_types=1);

namespace App\Domain\Entity;

use App\Domain\Exception\ValidationException;
use App\Domain\Security\OwnableResource;
use App\Domain\ValueObject\PaintingPlanId;
use App\Domain\ValueObject\PaintingSessionId;
use App\Domain\ValueObject\UserId;
use DateTimeImmutable;

class PaintingSession implements OwnableResource
{
    private function __construct(
        private PaintingSessionId $id,
        private PaintingPlanId $paintingPlanId,
        private UserId $userId,
        private int $durationSeconds,
        private DateTimeImmutable $workedAt,
    ) {
        if ($durationSeconds <= 0) {
            throw new ValidationException('La duración de la sesión debe ser mayor a 0');
        }
    }

    public static function record(
        PaintingPlanId $paintingPlanId,
        UserId $userId,
        int $durationSeconds,
        ?DateTimeImmutable $workedAt = null,
    ): self {
        return new self(
            PaintingSessionId::create(),
            $paintingPlanId,
            $userId,
            $durationSeconds,
            $workedAt ?? new DateTimeImmutable(),
        );
    }

    public function id(): PaintingSessionId { return $this->id; }
    public function paintingPlanId(): PaintingPlanId { return $this->paintingPlanId; }
    public function userId(): UserId { return $this->userId; }
    public function ownerId(): UserId { return $this->userId; }
    public function durationSeconds(): int { return $this->durationSeconds; }
    public function durationHours(): float { return $this->durationSeconds / 3600; }
    public function workedAt(): DateTimeImmutable { return $this->workedAt; }
}
