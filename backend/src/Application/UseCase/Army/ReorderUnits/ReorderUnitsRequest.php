<?php

declare(strict_types=1);

namespace App\Application\UseCase\Army\ReorderUnits;

use App\Domain\ValueObject\ProjectId;

final class ReorderUnitsRequest
{
    /** @param string[] $unitIds */
    public function __construct(private readonly string $projectId, private readonly array $unitIds) {}

    public function projectId(): ProjectId { return ProjectId::fromString($this->projectId); }
    /** @return string[] */
    public function unitIds(): array { return $this->unitIds; }
}
