<?php

declare(strict_types=1);

namespace App\Application\UseCase\WorkSession\CreateWorkSession;

use App\Application\UseCase\WorkSession\CreateWorkSession\CreateWorkSessionRequest;
use App\Application\UseCase\WorkSession\CreateWorkSession\CreateWorkSessionResponse;


final class CreateWorkSessionUseCase
{
    public function execute(CreateWorkSessionRequest $request): CreateWorkSessionResponse
    {
        // Retornar response
        return new CreateWorkSessionResponse(
            id: '123',
            name: $request->name,
            createdAt: (new \DateTime())->format('Y-m-d H:i:s')
        );
    }
}