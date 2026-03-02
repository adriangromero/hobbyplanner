<?php

declare(strict_types=1);

namespace App\Application\UseCase\Project\CreateProject;

use App\Application\DTO\ProjectDTO;
use App\Domain\Entity\Project;
use App\Domain\Repository\ProjectRepositoryInterface;
use App\Application\UseCase\Project\CreateProject\CreateProjectResponse;
use App\Application\UseCase\Project\CreateProject\CreateProjectRequest;
use App\Domain\ValueObject\ProjectId;
use App\Domain\Exception\ProjectNotFoundException;

final class CreateProjectUseCase
{
    public function __construct(
        private readonly ProjectRepositoryInterface $projectRepository,
    ) {}

    public function execute(CreateProjectRequest $request): CreateProjectResponse
    {
        // 2. Crear la entidad — validación en el constructor
        $project = new Project(
            ProjectId::create(),
            $request->userId(),
            $request->name(),
            $request->description(),
        );

        // 3. Persistir
        $this->projectRepository->save($project);

        // 4. Devolver Response con DTO
        return new CreateProjectResponse(
            ProjectDTO::fromEntity($project)
        );
    }

}
