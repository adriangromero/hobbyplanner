<?php

declare(strict_types=1);

namespace App\Domain\Entity;

use App\Domain\Exception\ValidationException;
use App\Domain\Security\OwnableResource;
use App\Domain\ValueObject\ArmyId;
use App\Domain\ValueObject\UserId;
use DateTimeImmutable;

class Army implements OwnableResource
{
    private DateTimeImmutable $createdAt;
    private DateTimeImmutable $updatedAt;

    private function __construct(private ArmyId $id, private UserId $userId, private string $name)
    {
        $this->createdAt = new DateTimeImmutable();
        $this->updatedAt = $this->createdAt;
        $this->rename($name);
    }

    public static function create(UserId $userId, string $name): self
    {
        return new self(ArmyId::create(), $userId, $name);
    }

    public function rename(string $name): void
    {
        if (trim($name) === '') {
            throw new ValidationException('El nombre del ejército no puede estar vacío');
        }
        $this->name = trim($name);
        $this->updatedAt = new DateTimeImmutable();
    }

    public function id(): ArmyId { return $this->id; }
    public function userId(): UserId { return $this->userId; }
    public function ownerId(): UserId { return $this->userId; }
    public function name(): string { return $this->name; }
    public function createdAt(): DateTimeImmutable { return $this->createdAt; }
    public function updatedAt(): DateTimeImmutable { return $this->updatedAt; }
}
