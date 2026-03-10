<?php

declare(strict_types=1);

namespace App\Application\UseCase\Project\GetProjectWithItems;

use App\Application\DTO\ItemDTO;
use App\Application\DTO\ProjectDTO;
use App\Application\DTO\WorkSessionDTO;
use App\Application\Security\OwnershipGuard;
use App\Domain\Entity\Item;
use App\Domain\Repository\ItemRepositoryInterface;
use App\Domain\Repository\ProjectRepositoryInterface;
use App\Domain\Exception\ProjectNotFoundException;
use App\Domain\Repository\WorkSessionRepositoryInterface;

final class GetProjectWithItemsUseCase
{
    public function __construct(
        private readonly ProjectRepositoryInterface     $projectRepository,
        private readonly ItemRepositoryInterface        $itemRepository,
        private readonly WorkSessionRepositoryInterface $sessionRepository,
        private readonly OwnershipGuard                 $ownershipGuard,
    ) {}

    public function execute(GetProjectWithItemsRequest $request): GetProjectWithItemsResponse
    {
        $projectId = $request->projectId();
        $project   = $this->projectRepository->findById($projectId);

        if ($project === null) {
            throw new ProjectNotFoundException($projectId->value());
        }

        $this->ownershipGuard->ensureOwnership($project);

        $items          = $this->itemRepository->findByProject($projectId);
        $sessionsByItem = $this->sessionRepository->findGroupedByItem($projectId);

        return new GetProjectWithItemsResponse(
            ProjectDTO::fromEntity($project),
            array_map(
                fn(Item $item) => $this->buildItemDTO($item, $sessionsByItem),
                $items
            )
        );
    }

    private function buildItemDTO(Item $item, array $sessionsByItem): ItemDTO
    {
        $sessions    = $sessionsByItem[$item->id()->value()] ?? [];
        $totalHours  = 0.0;
        $openSession = null;

        foreach ($sessions as $session) {
            if ($session->endedAt() !== null) {
                $totalHours += $session->durationHours();
            } else {
                $openSession = WorkSessionDTO::fromEntity($session);
            }
        }

        return ItemDTO::fromEntity(
            $item,
            count($sessions),
            $totalHours,
            $openSession,
        );
    }
}
