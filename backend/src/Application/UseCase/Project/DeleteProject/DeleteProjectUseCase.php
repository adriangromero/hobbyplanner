<?php

declare(strict_types=1);

namespace App\Application\UseCase\Project\DeleteProject;

use App\Application\Security\OwnershipGuard;
use App\Domain\Exception\ProjectNotFoundException;
use App\Domain\Repository\ItemRepositoryInterface;
use App\Domain\Repository\ProjectRepositoryInterface;
use App\Domain\Repository\WorkSessionRepositoryInterface;

final class DeleteProjectUseCase
{
    public function __construct(
        private readonly ProjectRepositoryInterface     $projectRepository,
        private readonly ItemRepositoryInterface        $itemRepository,
        private readonly WorkSessionRepositoryInterface $sessionRepository,
        private readonly OwnershipGuard                 $ownershipGuard,
    ) {}

    public function execute(DeleteProjectRequest $request): void
    {
        $project = $this->projectRepository->findById($request->projectId());

        if ($project === null) {
            throw new ProjectNotFoundException($request->projectId()->value());
        }

        $this->ownershipGuard->ensureOwnership($project);

        $this->sessionRepository->deleteByProject($request->projectId());
        $this->itemRepository->deleteByProject($request->projectId());
        $this->projectRepository->delete($project);
    }
}
