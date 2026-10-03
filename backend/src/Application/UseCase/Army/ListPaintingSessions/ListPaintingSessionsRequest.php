<?php

declare(strict_types=1);

namespace App\Application\UseCase\Army\ListPaintingSessions;

use App\Domain\ValueObject\PaintingPlanId;

final class ListPaintingSessionsRequest
{
    public function __construct(private readonly string $planId) {}

    public function planId(): PaintingPlanId { return PaintingPlanId::fromString($this->planId); }
}
