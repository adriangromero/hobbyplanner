<?php

declare(strict_types=1);

namespace App\Domain\Entity;

use App\Domain\Exception\ValidationException;
use App\Domain\Security\OwnableResource;
use App\Domain\ValueObject\ItemId;
use App\Domain\ValueObject\ItemStatus;
use App\Domain\ValueObject\UserId;
use App\Domain\ValueObject\ProjectId;
use DateTimeImmutable;

final class Item implements OwnableResource
{
    private ItemId            $id;
    private ProjectId         $projectId;
    private UserId            $userId;
    private string            $name;
    private float             $estimatedHours;
    private ItemStatus        $status;
    private DateTimeImmutable $createdAt;
    private DateTimeImmutable $updatedAt;

    private function __construct(
        ItemId    $id,
        ProjectId $projectId,
        UserId    $userId,
        string    $name,
        float     $estimatedHours,
    ) {
        $this->id        = $id;
        $this->projectId = $projectId;
        $this->userId    = $userId;
        $this->status    = ItemStatus::PENDING;
        $this->createdAt = new DateTimeImmutable();
        $this->updatedAt = new DateTimeImmutable();

        $this->rename($name);
        $this->updateEstimatedHours($estimatedHours);
    }

    public static function create(
        ProjectId $projectId,
        UserId    $userId,
        string    $name,
        float     $estimatedHours,
    ): self {
        return new self(ItemId::create(), $projectId, $userId, $name, $estimatedHours);
    }

    public function id(): ItemId                  { return $this->id; }
    public function projectId(): ProjectId         { return $this->projectId; }
    public function userId(): UserId               { return $this->userId; }
    public function ownerId(): UserId              { return $this->userId; }
    public function name(): string                 { return $this->name; }
    public function estimatedHours(): float        { return $this->estimatedHours; }
    public function status(): ItemStatus           { return $this->status; }
    public function createdAt(): DateTimeImmutable { return $this->createdAt; }
    public function updatedAt(): DateTimeImmutable { return $this->updatedAt; }

    public function update(string $name, float $estimatedHours): void
    {
        $this->rename($name);
        $this->updateEstimatedHours($estimatedHours);
    }

    public function markAsCompleted(): void
    {
        $this->status    = ItemStatus::COMPLETED;
        $this->updatedAt = new DateTimeImmutable();
    }

    public function markAsPending(): void
    {
        $this->status    = ItemStatus::PENDING;
        $this->updatedAt = new DateTimeImmutable();
    }

    public function rename(string $name): void
    {
        if (trim($name) === '') {
            throw new ValidationException('El nombre del item no puede estar vacío');
        }

        $this->name      = trim($name);
        $this->updatedAt = new DateTimeImmutable();
    }

    public function updateEstimatedHours(float $hours): void
    {
        if ($hours <= 0) {
            throw new ValidationException('Las horas estimadas deben ser mayores a 0');
        }

        $this->estimatedHours = $hours;
        $this->updatedAt      = new DateTimeImmutable();
    }
}
