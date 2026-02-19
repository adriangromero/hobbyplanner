<?php

declare(strict_types=1);

namespace App\Infrastructure\Controller\Api;

use App\Application\UseCase\WorkSession\ListWorkSessions\ListWorkSessionsRequest;
use App\Application\UseCase\WorkSession\ListWorkSessions\ListWorkSessionsResponse;
use App\Application\UseCase\WorkSession\ListWorkSessions\ListWorkSessionsUseCase;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api/work-sessions')]
final class WorkSessionController
{

    public function __construct(
        private readonly ListWorkSessionsUseCase $listWorkSessionsUseCase
    ) {
    }

    #[Route('', name: 'api_work_sessions_list', methods: ['GET'])]
    public function list(): JsonResponse
    {
        $response = $this->listWorkSessionsUseCase->execute(
            new ListWorkSessionsRequest()
        );
        
        return new JsonResponse([
            'workSessions' => array_map(fn($ws) => [
                'projectId' => $ws->projectId()->value(),
                'itemId' => $ws->itemId()->value(),
                'userId' => $ws->userId()->value(),
            ], $response->workSessions())
        ]);
    }
}
