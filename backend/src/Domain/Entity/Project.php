<?php

declare(strict_types=1);

namespace App\Domain\Entity;

use App\Domain\ValueObject\ProjectId;
use App\Domain\ValueObject\UserId;
use DateTimeImmutable;

final class Project
{
    private ProjectId $id;
    private UserId $userId;
    private string $name;
    private string $description;
    private DateTimeImmutable $createdAt;
    private DateTimeImmutable $updatedAt;

    public function __construct(ProjectId $id, UserId $userId, string $name, string $description)
    {
        $this->id        = $id;
        $this->userId    = $userId;
        $this->createdAt = new DateTimeImmutable();
        $this->updatedAt = new DateTimeImmutable();

        $this->rename($name);
        $this->updateDescription($description);
    }

    public function rename(string $name): void
    {
        if (trim($name) === '') {
            throw new \InvalidArgumentException('El nombre del proyecto no puede estar vacío');
        }

        $this->name      = trim($name);
        $this->updatedAt = new DateTimeImmutable();
    }

    public function updateDescription(string $description): void
    {
        $this->description = trim($description); // descripción puede estar vacía
        $this->updatedAt   = new DateTimeImmutable();
    }

    public function id(): ProjectId
    {
        return $this->id;
    }

    public function userId(): UserId
    {
        return $this->userId;
    }

    public function name(): string
    {
        return $this->name;
    }

    public function description(): string
    {
        return $this->description;
    }

    public function createdAt(): DateTimeImmutable
    {
        return $this->createdAt;
    }
    
    public function updatedAt(): DateTimeImmutable
    {
        return $this->updatedAt;
    }
}