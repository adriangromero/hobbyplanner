<?php

declare(strict_types=1);

namespace App\Domain\Entity;

use App\Domain\ValueObject\WorkSessionId;
use App\Domain\ValueObject\UserId;
use App\Domain\ValueObject\ProjectId;
use App\Domain\ValueObject\ItemId;
use DateTimeImmutable;

final class WorkSession
{
    public function __construct(
        private WorkSessionId      $id,
        private ProjectId          $projectId,
        private ItemId             $itemId,
        private UserId             $userId,
        private DateTimeImmutable  $startedAt,
        private ?DateTimeImmutable $endedAt,
    ) {}

    public function id(): WorkSessionId { return $this->id; }
    public function projectId(): ProjectId { return $this->projectId; }
    public function itemId(): ItemId { return $this->itemId; }
    public function userId(): UserId { return $this->userId; }
    public function startedAt(): DateTimeImmutable { return $this->startedAt; }
    public function endedAt(): ?DateTimeImmutable { return $this->endedAt; }

    public function finish(): void
    {
        $this->endedAt = new DateTimeImmutable();
    }

    public function update(DateTimeImmutable $startedAt, ?DateTimeImmutable $endedAt): void
    {
        $this->startedAt = $startedAt;
        $this->endedAt   = $endedAt;
    }

    public function durationSeconds(): int
    {
        if ($this->endedAt === null) {
            return 0;
        }

        return $this->endedAt->getTimestamp() - $this->startedAt->getTimestamp();
    }

    public function durationHours(): float
    {
        return $this->durationSeconds() / 3600;
    }

    public function workedDay(): string
    {
        return ($this->endedAt ?? $this->startedAt)->format('Y-m-d');
    }
}