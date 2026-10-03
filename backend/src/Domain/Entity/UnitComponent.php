<?php

declare(strict_types=1);

namespace App\Domain\Entity;

use App\Domain\Exception\ValidationException;
use App\Domain\Security\OwnableResource;
use App\Domain\ValueObject\UnitComponentId;
use App\Domain\ValueObject\UnitId;
use App\Domain\ValueObject\UserId;
use DateTimeImmutable;

final class UnitComponent implements OwnableResource
{
    private DateTimeImmutable $createdAt;
    private DateTimeImmutable $updatedAt;

    private function __construct(
        private UnitComponentId $id,
        private UnitId $unitId,
        private UserId $userId,
        private string $label,
        private int $quantityTotal,
        private int $quantityPainted,
    ) {
        $this->createdAt = new DateTimeImmutable();
        $this->updatedAt = $this->createdAt;
        $this->rename($label);
        $this->validateQuantities($quantityTotal, $quantityPainted);
    }

    public static function create(UnitId $unitId, UserId $userId, string $label, int $quantityTotal): self
    {
        return new self(UnitComponentId::create(), $unitId, $userId, $label, $quantityTotal, 0);
    }

    public function rename(string $label): void
    {
        $label = trim($label);
        if ($label === '') {
            throw new ValidationException('La etiqueta del componente no puede estar vacía');
        }
        $this->label = $label;
        $this->updatedAt = new DateTimeImmutable();
    }

    public function changeTotal(int $quantityTotal): void
    {
        $this->validateQuantities($quantityTotal, $this->quantityPainted);
        $this->quantityTotal = $quantityTotal;
        $this->updatedAt = new DateTimeImmutable();
    }

    public function updatePainted(int $quantityPainted): void
    {
        $this->validateQuantities($this->quantityTotal, $quantityPainted);
        $this->quantityPainted = $quantityPainted;
        $this->updatedAt = new DateTimeImmutable();
    }

    public function adjustPainted(int $delta): void
    {
        if ($delta !== 0) {
            $this->updatePainted($this->quantityPainted + $delta);
        }
    }

    private function validateQuantities(int $quantityTotal, int $quantityPainted): void
    {
        if ($quantityTotal < 1) {
            throw new ValidationException('La cantidad total debe ser al menos 1; elimina el componente si ya no existe');
        }
        if ($quantityPainted < 0 || $quantityPainted > $quantityTotal) {
            throw new ValidationException('La cantidad pintada debe estar entre 0 y la cantidad total');
        }
    }

    public function id(): UnitComponentId { return $this->id; }
    public function unitId(): UnitId { return $this->unitId; }
    public function userId(): UserId { return $this->userId; }
    public function ownerId(): UserId { return $this->userId; }
    public function label(): string { return $this->label; }
    public function quantityTotal(): int { return $this->quantityTotal; }
    public function quantityPainted(): int { return $this->quantityPainted; }
    public function remainingQuantity(): int { return $this->quantityTotal - $this->quantityPainted; }
    public function createdAt(): DateTimeImmutable { return $this->createdAt; }
    public function updatedAt(): DateTimeImmutable { return $this->updatedAt; }
}
