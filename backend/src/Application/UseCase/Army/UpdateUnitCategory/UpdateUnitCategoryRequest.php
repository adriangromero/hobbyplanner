<?php

declare(strict_types=1);

namespace App\Application\UseCase\Army\UpdateUnitCategory;

use App\Domain\ValueObject\UnitId;
use App\Domain\ValueObject\UnitCategory;

final class UpdateUnitCategoryRequest
{
    public function __construct(private readonly string $unitId, private readonly string $category) {}
    public function unitId(): UnitId { return UnitId::fromString($this->unitId); }
    public function category(): UnitCategory { return UnitCategory::fromValue($this->category); }
}
