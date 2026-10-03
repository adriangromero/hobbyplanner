<?php

declare(strict_types=1);

namespace App\Application\UseCase\Army\CreateUnit;

use App\Application\DTO\UnitDTO;
use App\Application\Security\OwnershipGuard;
use App\Domain\Entity\Unit;
use App\Domain\Exception\ProjectNotFoundException;
use App\Domain\Exception\ValidationException;
use App\Domain\Repository\ProjectRepositoryInterface;
use App\Domain\Repository\UnitRepositoryInterface;
use App\Domain\ValueObject\ProjectType;

final class CreateUnitUseCase
{
    public function __construct(private readonly ProjectRepositoryInterface $projects, private readonly UnitRepositoryInterface $units, private readonly OwnershipGuard $ownership) {}
    public function execute(CreateUnitRequest $request): UnitDTO
    {
        $project = $this->projects->findById($request->projectId()) ?? throw new ProjectNotFoundException($request->projectId()->value());
        $this->ownership->ensureOwnership($project);
        if ($project->type() !== ProjectType::ARMY) {
            throw new ValidationException('Solo los proyectos de tipo ejército pueden contener unidades');
        }
        $unit = Unit::create($project->id(), $request->userId(), $request->name());
        $this->units->save($unit);
        return UnitDTO::fromEntity($unit);
    }
}
