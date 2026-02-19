<?php

declare(strict_types=1);

namespace App\Domain\Entity;

use App\Domain\ValueObject\ItemId;
use App\Domain\ValueObject\UserId;
use App\Domain\ValueObject\ProjectId;
use App\Domain\ValueObject\ItemType;
use App\Domain\ValueObject\ItemStructure;
use App\Domain\ValueObject\ItemStatus;
use App\Domain\ValueObject\ItemPhases;
use DateTimeImmutable;

final class Item
{
    private ItemId $id;
    private ProjectId $projectId;
    private UserId $userId;
    private string $name;
    private float $estimatedHours;
    private DateTimeImmutable $createdAt;
    private DateTimeImmutable $updatedAt;

    public function __construct(
        ItemId $id,
        ProjectId $projectId,
        UserId $userId,
        string $name,
        float $estimatedHours
    ) {
        $this->id = $id;
        $this->projectId = $projectId;
        $this->userId = $userId;
        $this->name = $name;
        $this->estimatedHours = $estimatedHours;
        $this->createdAt = new DateTimeImmutable();
        $this->updatedAt = new DateTimeImmutable();
    }

    public function id(): ItemId { return $this->id; }
    public function projectId(): ProjectId { return $this->projectId; }
    public function name(): string { return $this->name; }
    public function userId(): UserId { return $this->userId; }
    public function estimatedHours(): float { return $this->estimatedHours; }
    public function createdAt(): DateTimeImmutable { return $this->createdAt; }
    public function updatedAt(): DateTimeImmutable { return $this->updatedAt; }
}
