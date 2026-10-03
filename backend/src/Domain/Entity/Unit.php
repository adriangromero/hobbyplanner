<?php

declare(strict_types=1);

namespace App\Domain\Entity;

use App\Domain\Exception\ValidationException;
use App\Domain\Security\OwnableResource;
use App\Domain\ValueObject\ProjectId;
use App\Domain\ValueObject\UnitId;
use App\Domain\ValueObject\UnitCategory;
use App\Domain\ValueObject\UserId;
use DateTimeImmutable;

final class Unit implements OwnableResource
{
    private DateTimeImmutable $createdAt;
    private DateTimeImmutable $updatedAt;

    private function __construct(
        private UnitId $id,
        private ProjectId $projectId,
        private UserId $userId,
        private string $name,
        private string $category,
        private int $position,
        private int $modelsPerRow,
    ) {
        $this->createdAt = new DateTimeImmutable();
        $this->updatedAt = $this->createdAt;
        $this->rename($name);
    }

    public static function create(
        ProjectId $projectId,
        UserId $userId,
        string $name,
        UnitCategory $category = UnitCategory::INFANTRY,
        int $position = 0,
        int $modelsPerRow = 5,
    ): self
    {
        if ($position < 0) {
            throw new ValidationException('La posición de la unidad no puede ser negativa');
        }
        self::assertModelsPerRow($modelsPerRow);

        return new self(UnitId::create(), $projectId, $userId, $name, $category->value, $position, $modelsPerRow);
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

    public function changeCategory(UnitCategory $category): void
    {
        $this->category = $category->value;
        $this->updatedAt = new DateTimeImmutable();
    }

    public function reposition(int $position): void
    {
        if ($position < 0) {
            throw new ValidationException('La posición de la unidad no puede ser negativa');
        }
        $this->position = $position;
        $this->updatedAt = new DateTimeImmutable();
    }

    public function changeModelsPerRow(int $modelsPerRow): void
    {
        self::assertModelsPerRow($modelsPerRow);
        $this->modelsPerRow = $modelsPerRow;
        $this->updatedAt = new DateTimeImmutable();
    }

    private static function assertModelsPerRow(int $modelsPerRow): void
    {
        if ($modelsPerRow < 1 || $modelsPerRow > 12) {
            throw new ValidationException('La fila debe tener entre 1 y 12 miniaturas');
        }
    }

    public function id(): UnitId { return $this->id; }
    public function projectId(): ProjectId { return $this->projectId; }
    public function userId(): UserId { return $this->userId; }
    public function ownerId(): UserId { return $this->userId; }
    public function name(): string { return $this->name; }
    public function category(): UnitCategory { return UnitCategory::from($this->category); }
    public function position(): int { return $this->position; }
    public function modelsPerRow(): int { return $this->modelsPerRow; }
    public function createdAt(): DateTimeImmutable { return $this->createdAt; }
    public function updatedAt(): DateTimeImmutable { return $this->updatedAt; }
}
