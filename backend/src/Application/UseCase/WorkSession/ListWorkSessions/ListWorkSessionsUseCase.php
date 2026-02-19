<?php

declare(strict_types=1);

namespace App\Application\UseCase\WorkSession\ListWorkSessions;

use App\Domain\Repository\WorkSessionRepositoryInterface;

final class ListWorkSessionsUseCase
{
    public function __construct(
        private WorkSessionRepositoryInterface $workSessionRepository
    ) {}

    public function execute(ListWorkSessionsRequest $request): ListWorkSessionsResponse
    {
        $workSessions = $this->workSessionRepository->findAll();

        return new ListWorkSessionsResponse($workSessions);
    }
}
