<?php

declare(strict_types=1);

namespace App\Application\UseCase\Project\GetProjectWithItems;

use App\Application\DTO\ItemDTO;
use App\Application\DTO\ProjectDTO;
use App\Application\DTO\WorkSessionDTO;
use App\Domain\Entity\Item;
use App\Domain\Entity\WorkSession;
use App\Domain\Repository\ItemRepositoryInterface;
use App\Domain\Repository\ProjectRepositoryInterface;
use App\Domain\Repository\WorkSessionRepositoryInterface;

final class GetProjectWithItemsUseCase
{
    public function __construct(
        private readonly ProjectRepositoryInterface     $projectRepository,
        private readonly ItemRepositoryInterface        $itemRepository,
        private readonly WorkSessionRepositoryInterface $sessionRepository,
    ) {}

    public function execute(GetProjectWithItemsRequest $request): GetProjectWithItemsResponse
    {
        $projectId      = $request->projectId();
        $project        = $this->projectRepository->findById($projectId);
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
        $sessions = $sessionsByItem[$item->id()->value()] ?? [];

        return ItemDTO::fromEntity(
            $item,
            count($sessions),
            array_map(
                fn(WorkSession $session) => WorkSessionDTO::fromEntity($session),
                $sessions
            )
        );
    }
}