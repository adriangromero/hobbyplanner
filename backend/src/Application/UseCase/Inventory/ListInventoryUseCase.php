<?php

declare(strict_types=1);

namespace App\Application\UseCase\Inventory;

use App\Application\DTO\ItemDTO;
use App\Application\DTO\WorkSessionDTO;
use App\Domain\Entity\Item;
use App\Domain\Repository\ItemRepositoryInterface;
use App\Domain\Repository\ProjectRepositoryInterface;
use App\Domain\Repository\WorkSessionRepositoryInterface;

final class ListInventoryUseCase
{
    public function __construct(
        private readonly ItemRepositoryInterface        $itemRepository,
        private readonly ProjectRepositoryInterface     $projectRepository,
        private readonly WorkSessionRepositoryInterface $sessionRepository,
    ) {}

    public function execute(ListInventoryRequest $request): ListInventoryResponse
    {
        $items    = $this->itemRepository->findByUser($request->userId());
        $projects = $this->projectRepository->findByUser($request->userId());

        // Index project names by ID
        $projectNames = [];
        foreach ($projects as $project) {
            $projectNames[$project->id()->value()] = $project->name();
        }

        $itemDTOs = array_map(
            function (Item $item) use ($projectNames) {
                $sessions   = $this->sessionRepository->findByItem($item->id());
                $totalHours = 0.0;
                $completed  = 0;

                foreach ($sessions as $session) {
                    if ($session->endedAt() !== null) {
                        $totalHours += $session->durationHours();
                        $completed++;
                    }
                }

                return ItemDTO::fromEntity(
                    $item,
                    $completed,
                    $totalHours,
                    null,
                    $projectNames[$item->projectId()->value()] ?? '',
                );
            },
            $items
        );

        return new ListInventoryResponse($itemDTOs);
    }
}
