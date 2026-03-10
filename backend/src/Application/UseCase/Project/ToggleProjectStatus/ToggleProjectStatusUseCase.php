<?php

declare(strict_types=1);

namespace App\Application\UseCase\Project\ToggleProjectStatus;

use App\Application\DTO\ProjectDTO;
use App\Application\Security\OwnershipGuard;
use App\Domain\Exception\ProjectNotFoundException;
use App\Domain\Repository\ProjectRepositoryInterface;
use App\Domain\ValueObject\ProjectStatus;

final class ToggleProjectStatusUseCase
{
    public function __construct(
        private readonly ProjectRepositoryInterface $projectRepository,
        private readonly OwnershipGuard             $ownershipGuard,
    ) {}

    public function execute(ToggleProjectStatusRequest $request): ToggleProjectStatusResponse
    {
        $project = $this->projectRepository->findById($request->projectId());

        if ($project === null) {
            throw new ProjectNotFoundException($request->projectId()->value());
        }

        $this->ownershipGuard->ensureOwnership($project);

        if ($project->status() === ProjectStatus::COMPLETED) {
            $project->markAsActive();
        } else {
            $project->markAsCompleted();
        }

        $this->projectRepository->save($project);

        return new ToggleProjectStatusResponse(
            ProjectDTO::fromEntity($project)
        );
    }
}
