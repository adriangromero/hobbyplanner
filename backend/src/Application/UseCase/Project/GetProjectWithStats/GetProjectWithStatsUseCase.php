<?php

namespace App\Application\UseCase\Project\GetProjectWithStats;

use App\Domain\Repository\ProjectRepositoryInterface;
use App\Domain\ValueObject\ProjectId;
use App\Domain\ValueObject\ProjectStatsCalculator;

final readonly class GetProjectWithStatsUseCase
{
    public function __construct(
        private ProjectRepositoryInterface $projectRepository,
        private ProjectStatsCalculator $statsCalculator
    ) {}
    
    public function execute(ProjectId $projectId): GetProjectWithStatsResponse
    {
        $project = $this->projectRepository->findById($projectId);
        
        // Domain Service calcula stats
        $stats = $this->statsCalculator->calculate($projectId);
        
        return new GetProjectWithStatsResponse(
            id: $project->id()->value(),
            name: $project->name(),
            description: $project->description(),
            createdAt: $project->createdAt()->format('Y-m-d H:i:s'),
            stats: $stats
        );
    }
}