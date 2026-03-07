<?php

declare(strict_types=1);

namespace App\Application\UseCase\Project\ListProjects;

use App\Application\DTO\ProjectDTO;
use App\Domain\Entity\Project;
use App\Domain\Repository\ProjectRepositoryInterface;

final class ListProjectsUseCase
{
    public function __construct(
        private readonly ProjectRepositoryInterface $projectRepository
    ) {}

    public function execute(ListProjectsRequest $request): ListProjectsResponse
    {
        $projects = $this->projectRepository->findByUser($request->userId());

        return new ListProjectsResponse(
            array_map(
                fn(Project $project) => ProjectDTO::fromEntity($project),
                $projects
            )
        );
    }
}