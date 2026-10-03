<?php

declare(strict_types=1);

namespace App\Application\UseCase\Army\UpdatePaintingPlan;

use App\Application\DTO\PaintingPlanDTO;
use App\Application\Security\OwnershipGuard;
use App\Domain\Exception\PaintingPlanNotFoundException;
use App\Domain\Repository\PaintingPlanRepositoryInterface;

final class UpdatePaintingPlanUseCase
{
    public function __construct(
        private readonly PaintingPlanRepositoryInterface $plans,
        private readonly OwnershipGuard $ownership,
    ) {}

    public function execute(UpdatePaintingPlanRequest $request): PaintingPlanDTO
    {
        $plan = $this->plans->findById($request->planId())
            ?? throw new PaintingPlanNotFoundException($request->planId()->value());
        $this->ownership->ensureOwnership($plan);
        $plan->updateEstimate($request->estimatedHours());
        $this->plans->save($plan);

        return PaintingPlanDTO::fromEntity($plan);
    }
}
