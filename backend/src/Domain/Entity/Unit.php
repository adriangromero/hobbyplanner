<?php

declare(strict_types=1);

namespace App\Domain\Entity;

use App\Domain\Exception\ValidationException;
use App\Domain\Security\OwnableResource;
use App\Domain\ValueObject\ArmyId;
use App\Domain\ValueObject\UnitId;
use App\Domain\ValueObject\UserId;
use DateTimeImmutable;

final class Unit implements OwnableResource
{
    private DateTimeImmutable $createdAt;
    private DateTimeImmutable $updatedAt;

    private function __construct(
        private UnitId $id,
        private ArmyId $armyId,
        private UserId $userId,
        private string $name,
    ) {
        $this->createdAt = new DateTimeImmutable();
        $this->updatedAt = $this->createdAt;
        $this->rename($name);
    }

    public static function create(ArmyId $armyId, UserId $userId, string $name): self
    {
        return new self(UnitId::create(), $armyId, $userId, $name);
    }

    public function rename(string $name): void
    {
        $name = trim($name);
        if ($name === '') {
            throw new ValidationException('El nombre de la unidad no puede estar vacío');
        }
        $this->name = $name;
        $this->updatedAt = new DateTimeImmutable();
    }

    public function id(): UnitId { return $this->id; }
    public function armyId(): ArmyId { return $this->armyId; }
    public function userId(): UserId { return $this->userId; }
    public function ownerId(): UserId { return $this->userId; }
    public function name(): string { return $this->name; }
    public function createdAt(): DateTimeImmutable { return $this->createdAt; }
    public function updatedAt(): DateTimeImmutable { return $this->updatedAt; }
}
