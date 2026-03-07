<?php

declare(strict_types=1);

namespace App\Application\UseCase\Item\GetItemSessions;

use App\Application\DTO\WorkSessionDTO;
use App\Domain\Entity\WorkSession;
use App\Domain\Repository\WorkSessionRepositoryInterface;

final class GetItemSessionsUseCase
{
    public function __construct(
        private readonly WorkSessionRepositoryInterface $sessionRepository,
    ) {}

    public function execute(GetItemSessionsRequest $request): GetItemSessionsResponse
    {
        $sessions = $this->sessionRepository->findByItem($request->itemId());

        return new GetItemSessionsResponse(
            array_map(
                fn(WorkSession $session) => WorkSessionDTO::fromEntity($session),
                $sessions
            )
        );
    }
}
