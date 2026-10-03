<?php

declare(strict_types=1);

namespace App\Application\UseCase\Army\AddUnitComponent;

use App\Domain\ValueObject\UnitId;
use App\Domain\ValueObject\UserId;

final class AddUnitComponentRequest
{
    public function __construct(private readonly string $unitId, private readonly string $userId, private readonly string $label, private readonly int $quantityTotal) {}
    public function unitId(): UnitId { return UnitId::fromString($this->unitId); }
    public function userId(): UserId { return UserId::fromString($this->userId); }
    public function label(): string { return $this->label; }
    public function quantityTotal(): int { return $this->quantityTotal; }
}
