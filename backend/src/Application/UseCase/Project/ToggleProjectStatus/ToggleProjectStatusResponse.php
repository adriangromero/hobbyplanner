<?php

declare(strict_types=1);

namespace App\Application\UseCase\Project\ToggleProjectStatus;

use App\Application\DTO\ProjectDTO;

final class ToggleProjectStatusResponse
{
    public function __construct(
        private readonly ProjectDTO $project,
    ) {}

    public function project(): ProjectDTO { return $this->project; }
}
