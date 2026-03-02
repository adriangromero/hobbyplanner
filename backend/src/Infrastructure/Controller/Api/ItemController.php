<?php

declare(strict_types=1);

namespace App\Infrastructure\Controller\Api;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Domain\Exception\ProjectNotFoundException;
use App\Application\UseCase\Item\CreateItem\CreateItemRequest;
use App\Application\UseCase\Item\CreateItem\CreateItemUseCase;
use App\Application\UseCase\Item\UpdateItem\UpdateItemRequest;
use App\Application\UseCase\Item\UpdateItem\UpdateItemUseCase;
use App\Application\UseCase\Item\DeleteItem\DeleteItemRequest;
use App\Application\UseCase\Item\DeleteItem\DeleteItemUseCase;
use App\Domain\Exception\ItemNotFoundException;

#[Route('/api/items')]
final class ItemController extends ApiController
{
    #[Route('', name: 'api_create_item', methods: ['POST'])]
    public function create(
        Request $request,
        CreateItemUseCase $useCase,
    ): JsonResponse {
        $data = json_decode($request->getContent(), true);

        $error = $this->validateRequired($data, ['name', 'estimatedHours', 'projectId']);
        if ($error) return $error;

        $currentUser = $this->getCurrentUser();

        try {
            $response = $useCase->execute(new CreateItemRequest(
                projectId:      $data['projectId'],
                userId:         $currentUser->id()->value(),
                name:           $data['name'],
                estimatedHours: (float) $data['estimatedHours'],
            ));

            $dto = $response->item();

            return new JsonResponse([
                'id'             => $dto->id,
                'name'           => $dto->name,
                'estimatedHours' => $dto->estimatedHours,
                'totalSessions'  => 0,
                'sessions'       => [],
            ], Response::HTTP_CREATED);

        } catch (ProjectNotFoundException $e) {
            return new JsonResponse(['error' => $e->getMessage()], Response::HTTP_NOT_FOUND);
        } catch (\InvalidArgumentException $e) {
            return new JsonResponse(['error' => $e->getMessage()], Response::HTTP_UNPROCESSABLE_ENTITY);
        }
    }    

    #[Route('/{id}', name: 'api_item_update', methods: ['PUT'])]
    public function update(
        string $id,
        Request $request,
        UpdateItemUseCase $useCase,
    ): JsonResponse {
        $data = json_decode($request->getContent(), true);

        $error = $this->validateRequired($data, ['name', 'estimatedHours']);
        if ($error) return $error;

        try {
            $response = $useCase->execute(new UpdateItemRequest(
                itemId:      $id,
                name:        $data['name'],
                estimatedHours: (float) $data['estimatedHours'],
            ));

            $dto = $response->item();

            return new JsonResponse([
                'id'             => $dto->id,
                'name'           => $dto->name,
                'estimatedHours' => $dto->estimatedHours,
            ]);

        } catch (ItemNotFoundException $e) {
            return new JsonResponse(
                ['error' => $e->getMessage()],
                Response::HTTP_NOT_FOUND
            );
        }
    }

    #[Route('/{id}', name: 'api_item_delete', methods: ['DELETE'])]
    public function delete(
        string $id,
        DeleteItemUseCase $useCase,
    ): JsonResponse {
        try {
            $useCase->execute(new DeleteItemRequest($id));

            return new JsonResponse(null, Response::HTTP_NO_CONTENT);

        } catch (ItemNotFoundException $e) {
            return new JsonResponse(
                ['error' => $e->getMessage()],
                Response::HTTP_NOT_FOUND
            );
        }
    }
}
