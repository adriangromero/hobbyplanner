<?php

declare(strict_types=1);

namespace App\Application\UseCase\Army\RemoveUnitComponent;

use App\Application\Security\OwnershipGuard;
use App\Domain\Exception\UnitComponentNotFoundException;
use App\Domain\Repository\UnitComponentRepositoryInterface;

final class RemoveUnitComponentUseCase
{
    public function __construct(private readonly UnitComponentRepositoryInterface $components, private readonly OwnershipGuard $ownership) {}
    public function execute(RemoveUnitComponentRequest $request): void
    {
        $component = $this->components->findById($request->componentId()) ?? throw new UnitComponentNotFoundException($request->componentId()->value());
        $this->ownership->ensureOwnership($component);
        $this->components->remove($component);
    }
}
