<?php

declare(strict_types=1);

namespace App\Application\UseCase\Project\CreateProject;

use App\Application\DTO\ProjectDTO;
use App\Domain\Entity\Project;
use App\Domain\Repository\ProjectRepositoryInterface;

final class CreateProjectUseCase
{
    public function __construct(
        private readonly ProjectRepositoryInterface $projectRepository,
    ) {}

    public function execute(CreateProjectRequest $request): CreateProjectResponse
    {
        $project = Project::create(
            $request->userId(),
            $request->name(),
            $request->description(),
        );

        $this->projectRepository->save($project);

        return new CreateProjectResponse(
            ProjectDTO::fromEntity($project)
        );
    }
}
