<?php

declare(strict_types=1);

namespace App\Application\UseCase\Project\UpdateProject;

use App\Application\DTO\ProjectDTO;
use App\Application\Security\OwnershipGuard;
use App\Domain\Exception\ProjectNotFoundException;
use App\Domain\Repository\ProjectRepositoryInterface;

final class UpdateProjectUseCase
{
    public function __construct(
        private readonly ProjectRepositoryInterface $projectRepository,
        private readonly OwnershipGuard             $ownershipGuard,
    ) {}

    public function execute(UpdateProjectRequest $request): UpdateProjectResponse
    {
        $project = $this->projectRepository->findById($request->projectId());

        if ($project === null) {
            throw new ProjectNotFoundException($request->projectId()->value());
        }

        $this->ownershipGuard->ensureOwnership($project);

        $project->rename($request->name());
        $project->updateDescription($request->description());

        $this->projectRepository->save($project);

        return new UpdateProjectResponse(
            ProjectDTO::fromEntity($project)
        );
    }
}
