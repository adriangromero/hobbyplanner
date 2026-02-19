<?php

declare(strict_types=1);

namespace App\Application\UseCase\Project\CreateProject;

final class CreateProjectUseCase
{
    public function __construct(
    ) {}

    public function execute(CreateProjectRequest $request): CreateProjectResponse
    {
        return new CreateProjectResponse(
            'project-id',
            $request->name,
            date('Y-m-d H:i:s')
        );
    }
}