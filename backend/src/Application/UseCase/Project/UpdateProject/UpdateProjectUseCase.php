<?php

declare(strict_types=1);

namespace App\Application\UseCase\Project\UpdateProject;

use App\Application\DTO\ProjectDTO;
use App\Domain\Exception\ProjectNotFoundException;
use App\Domain\Repository\ProjectRepositoryInterface;

final class UpdateProjectUseCase
{
    public function __construct(
        private readonly ProjectRepositoryInterface $projectRepository,
    ) {}

    public function execute(UpdateProjectRequest $request): UpdateProjectResponse
    {
        $project = $this->projectRepository->findById($request->projectId());

        if ($project === null) {
            throw new ProjectNotFoundException($request->projectId()->value());
        }

        $project->rename($request->name());
        $project->updateDescription($request->description());

        $this->projectRepository->save($project);

        return new UpdateProjectResponse(
            ProjectDTO::fromEntity($project)
        );
    }
}
