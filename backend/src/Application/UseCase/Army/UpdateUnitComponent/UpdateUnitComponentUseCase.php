<?php

declare(strict_types=1);

namespace App\Application\UseCase\Army\UpdateUnitComponent;

use App\Application\DTO\UnitComponentDTO;
use App\Application\Security\OwnershipGuard;
use App\Domain\Exception\UnitComponentNotFoundException;
use App\Domain\Repository\UnitComponentRepositoryInterface;

final class UpdateUnitComponentUseCase
{
    public function __construct(private readonly UnitComponentRepositoryInterface $components, private readonly OwnershipGuard $ownership) {}
    public function execute(UpdateUnitComponentRequest $request): UnitComponentDTO
    {
        $component = $this->components->findById($request->componentId()) ?? throw new UnitComponentNotFoundException($request->componentId()->value());
        $this->ownership->ensureOwnership($component);
        $component->changeTotal($request->quantityTotal());
        $component->rename($request->label());
        $this->components->save($component);
        return UnitComponentDTO::fromEntity($component);
    }
}
