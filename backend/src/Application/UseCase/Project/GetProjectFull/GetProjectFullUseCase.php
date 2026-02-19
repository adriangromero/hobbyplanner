<?php

declare(strict_types=1);

namespace App\Application\UseCase\Project\GetProjectFull;

use App\Domain\Repository\ItemRepositoryInterface;
use App\Domain\Repository\ProjectRepositoryInterface;

final class GetProjectFullUseCase
{
    public function __construct(
        private ProjectRepositoryInterface $projectRepository,
        private ItemRepositoryInterface $itemsRepository,
    ) {}

    public function execute(GetProjectFullRequest $request): GetProjectFullResponse
    {
        $project = $this->projectRepository->findById($request->projectId());
        $items = $this->itemsRepository->findByProject($request->projectId()); 
        $sessions = array();

        return new GetProjectFullResponse($project, $items, $sessions);
    }

}
