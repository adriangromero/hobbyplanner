<?php

declare(strict_types=1);

namespace App\Application\UseCase\Project\ListProjects;

use App\Domain\Repository\ProjectRepositoryInterface;

final class ListProjectsUseCase
{
    public function __construct(
        private ProjectRepositoryInterface $projectRepository
    ) {}

    public function execute(ListProjectsRequest $request): ListProjectsResponse
    {
        $projects = $this->projectRepository->findAll();

        return new ListProjectsResponse($projects);
    }
}
