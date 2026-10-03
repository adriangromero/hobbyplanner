<?php

declare(strict_types=1);

namespace App\Application\UseCase\Army\ListPaintingSessions;

use App\Application\DTO\PaintingSessionDTO;
use App\Application\Security\OwnershipGuard;
use App\Domain\Exception\PaintingPlanNotFoundException;
use App\Domain\Repository\PaintingPlanRepositoryInterface;
use App\Domain\Repository\PaintingSessionRepositoryInterface;

final class ListPaintingSessionsUseCase
{
    public function __construct(
        private readonly PaintingPlanRepositoryInterface $plans,
        private readonly PaintingSessionRepositoryInterface $sessions,
        private readonly OwnershipGuard $ownership,
    ) {}

    public function execute(ListPaintingSessionsRequest $request): array
    {
        $plan = $this->plans->findById($request->planId())
            ?? throw new PaintingPlanNotFoundException($request->planId()->value());
        $this->ownership->ensureOwnership($plan);

        return array_map(
            PaintingSessionDTO::fromEntity(...),
            $this->sessions->findByPlan($plan->id()),
        );
    }
}
