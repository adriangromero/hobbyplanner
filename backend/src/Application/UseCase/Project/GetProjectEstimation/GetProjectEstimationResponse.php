<?php

declare(strict_types=1);

namespace App\Application\UseCase\Project\GetProjectEstimation;

use App\Application\DTO\ProjectEstimationDTO;

final class GetProjectEstimationResponse
{
    public function __construct(
        private readonly ProjectEstimationDTO $estimation,
    ) {}

    public function estimation(): ProjectEstimationDTO
    {
        return $this->estimation;
    }
}