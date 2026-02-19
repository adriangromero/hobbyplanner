<?php

declare(strict_types=1);

namespace App\Domain\Entity;

use App\Domain\ValueObject\PaintingSessionId;
use App\Domain\ValueObject\WorkSessionId;
use App\Domain\ValueObject\UserId;
use App\Domain\ValueObject\ProjectId;
use App\Domain\ValueObject\ItemId;
use DateTimeImmutable;

final class WorkSession
{
    public function __construct(
        private WorkSessionId $id,
        private ProjectId $projectId,
        private ItemId $itemId,
        private UserId $userId,
        private float $hours,
        private \DateTimeImmutable $workedAt,
        private \DateTimeImmutable $createdAt
    ) {}

    public function id(): WorkSessionId { return $this->id; }
    public function projectId(): ProjectId { return $this->projectId; }
    public function itemId(): ItemId { return $this->itemId; }
    public function userId(): UserId { return $this->userId; }
    public function hours(): float { return $this->hours; }
    public function workedAt(): \DateTimeImmutable { return $this->workedAt; }
    public function createdAt(): \DateTimeImmutable { return $this->createdAt; }
}
