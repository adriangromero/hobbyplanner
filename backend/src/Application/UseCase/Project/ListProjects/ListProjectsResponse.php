<?php

declare(strict_types=1);

namespace App\Application\UseCase\Project\ListProjects;

use App\Domain\Entity\Project;

final class ListProjectsResponse
{
    /** @var Project[] */
    private array $projects;

    /**
     * @param Project[] $projects
     */
    public function __construct(array $projects)
    {
        $this->projects = $projects;
    }

    /**
     * @return Project[]
     */
    public function projects(): array
    {
        return $this->projects;
    }
}
