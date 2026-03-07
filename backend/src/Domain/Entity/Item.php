<?php

declare(strict_types=1);

namespace App\Domain\Entity;

use App\Domain\Exception\ValidationException;
use App\Domain\ValueObject\ItemId;
use App\Domain\ValueObject\UserId;
use App\Domain\ValueObject\ProjectId;
use DateTimeImmutable;

final class Item
{
    private ItemId            $id;
    private ProjectId         $projectId;
    private UserId            $userId;
    private string            $name;
    private float             $estimatedHours;
    private DateTimeImmutable $createdAt;
    private DateTimeImmutable $updatedAt;

    public function __construct(
        ItemId    $id,
        ProjectId $projectId,
        UserId    $userId,
        string    $name,
        float     $estimatedHours,
    ) {
        $this->id        = $id;
        $this->projectId = $projectId;
        $this->userId    = $userId;
        $this->createdAt = new DateTimeImmutable();
        $this->updatedAt = new DateTimeImmutable();

        // Usamos los métodos para no duplicar validación
        $this->rename($name);
        $this->updateEstimatedHours($estimatedHours);
    }

    public function id(): ItemId                  { return $this->id; }
    public function projectId(): ProjectId         { return $this->projectId; }
    public function userId(): UserId               { return $this->userId; }
    public function name(): string                 { return $this->name; }
    public function estimatedHours(): float        { return $this->estimatedHours; }
    public function createdAt(): DateTimeImmutable { return $this->createdAt; }
    public function updatedAt(): DateTimeImmutable { return $this->updatedAt; }

    public function update(string $name, float $estimatedHours): void
    {
        $this->rename($name);
        $this->updateEstimatedHours($estimatedHours);
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