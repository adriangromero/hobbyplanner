<?php

declare(strict_types=1);

namespace App\Application\UseCase\Project\ListProjects;

use App\Application\DTO\ProjectDTO;
use App\Domain\Entity\Project;
use App\Domain\Repository\ProjectRepositoryInterface;
use App\Domain\Repository\UnitRepositoryInterface;
use App\Domain\ValueObject\ProjectType;

final class ListProjectsUseCase
{
    public function __construct(
        private readonly ProjectRepositoryInterface $projectRepository,
        private readonly UnitRepositoryInterface $unitRepository,
    ) {}

    public function execute(ListProjectsRequest $request): ListProjectsResponse
    {
        $projects = $this->projectRepository->findByUser($request->userId());
        $armyProjects = array_values(array_filter(
            $projects,
            static fn(Project $project): bool => $project->type() === ProjectType::ARMY,
        ));
        $summaries = $this->unitRepository->inventorySummaryByProjects(
            array_map(static fn(Project $project) => $project->id(), $armyProjects),
        );

        return new ListProjectsResponse(
            array_map(
                static fn(Project $project): ProjectDTO => ProjectDTO::fromEntity(
                    $project,
                    $summaries[$project->id()->value()]['unitCount'] ?? 0,
                    $summaries[$project->id()->value()]['miniatureCount'] ?? 0,
                ),
                $projects
            )
        );
    }
}
