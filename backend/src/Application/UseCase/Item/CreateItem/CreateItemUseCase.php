<?php

declare(strict_types=1);

namespace App\Application\UseCase\Item\CreateItem;

use App\Application\DTO\ItemDTO;
use App\Application\Security\OwnershipGuard;
use App\Domain\Entity\Item;
use App\Domain\Exception\ProjectNotFoundException;
use App\Domain\Repository\ItemRepositoryInterface;
use App\Domain\Repository\ProjectRepositoryInterface;

final class CreateItemUseCase
{
    public function __construct(
        private readonly ProjectRepositoryInterface $projectRepository,
        private readonly ItemRepositoryInterface    $itemRepository,
        private readonly OwnershipGuard             $ownershipGuard,
    ) {}

    public function execute(CreateItemRequest $request): CreateItemResponse
    {
        $project = $this->projectRepository->findById($request->projectId());

        if ($project === null) {
            throw new ProjectNotFoundException($request->projectId()->value());
        }

        $this->ownershipGuard->ensureOwnership($project);

        $item = Item::create(
            $request->projectId(),
            $request->userId(),
            $request->name(),
            $request->estimatedHours(),
        );

        $this->itemRepository->save($item);

        return new CreateItemResponse(
            ItemDTO::fromEntity($item)
        );
    }
}
