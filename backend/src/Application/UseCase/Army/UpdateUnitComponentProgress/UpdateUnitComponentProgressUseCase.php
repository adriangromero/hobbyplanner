<?php

declare(strict_types=1);

namespace App\Application\UseCase\Army\UpdateUnitComponentProgress;

use App\Application\DTO\UnitComponentDTO;
use App\Application\Security\OwnershipGuard;
use App\Domain\Exception\UnitComponentNotFoundException;
use App\Domain\Repository\UnitComponentRepositoryInterface;

final class UpdateUnitComponentProgressUseCase
{
    public function __construct(private readonly UnitComponentRepositoryInterface $components, private readonly OwnershipGuard $ownership) {}
    public function execute(UpdateUnitComponentProgressRequest $request): UnitComponentDTO
    {
        $component = $this->components->findById($request->componentId()) ?? throw new UnitComponentNotFoundException($request->componentId()->value());
        $this->ownership->ensureOwnership($component);
        $component->adjustPainted($request->delta());
        $this->components->save($component);
        return UnitComponentDTO::fromEntity($component);
    }
}
