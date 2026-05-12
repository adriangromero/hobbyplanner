<?php

declare(strict_types=1);

namespace App\Domain\Entity;

use App\Domain\Exception\ValidationException;
use App\Domain\Security\OwnableResource;
use App\Domain\ValueObject\ProjectId;
use App\Domain\ValueObject\ProjectStatus;
use App\Domain\ValueObject\UserId;
use DateTimeImmutable;

final class Project implements OwnableResource
{
    private ProjectId $id;
    private UserId $userId;
    private string $name;
    private string $description;
    private ProjectStatus $status;
    private DateTimeImmutable $createdAt;
    private DateTimeImmutable $updatedAt;

    private function __construct(ProjectId $id, UserId $userId, string $name, string $description)
    {
        $this->id        = $id;
        $this->userId    = $userId;
        $this->status    = ProjectStatus::ACTIVE;
        $this->createdAt = new DateTimeImmutable();
        $this->updatedAt = new DateTimeImmutable();

        $this->rename($name);
        $this->updateDescription($description);
    }

    public static function create(UserId $userId, string $name, string $description): self
    {
        return new self(ProjectId::create(), $userId, $name, $description);
    }

    public function rename(string $name): void
    {
        if (trim($name) === '') {
            throw new ValidationException('El nombre del proyecto no puede estar vacío');
        }

        $this->name      = trim($name);
        $this->updatedAt = new DateTimeImmutable();
    }

    public function updateDescription(string $description): void
    {
        $this->description = trim($description); // descripción puede estar vacía
        $this->updatedAt   = new DateTimeImmutable();
    }

    public function markAsCompleted(): void
    {
        $this->status    = ProjectStatus::COMPLETED;
        $this->updatedAt = new DateTimeImmutable();
    }

    public function markAsActive(): void
    {
        $this->status    = ProjectStatus::ACTIVE;
        $this->updatedAt = new DateTimeImmutable();
    }

    public function id(): ProjectId              { return $this->id; }
    public function userId(): UserId             { return $this->userId; }
    public function ownerId(): UserId            { return $this->userId; }
    public function name(): string               { return $this->name; }
    public function description(): string        { return $this->description; }
    public function status(): ProjectStatus      { return $this->status; }
    public function createdAt(): DateTimeImmutable { return $this->createdAt; }
    public function updatedAt(): DateTimeImmutable { return $this->updatedAt; }
}
