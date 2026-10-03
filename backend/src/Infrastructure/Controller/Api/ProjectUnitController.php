<?php

declare(strict_types=1);

namespace App\Infrastructure\Controller\Api;

use App\Application\DTO\UnitDTO;
use App\Application\UseCase\Army\AddUnitComponent\AddUnitComponentRequest;
use App\Application\UseCase\Army\AddUnitComponent\AddUnitComponentUseCase;
use App\Application\UseCase\Army\CreatePaintingPlan\CreatePaintingPlanRequest;
use App\Application\UseCase\Army\CreatePaintingPlan\CreatePaintingPlanUseCase;
use App\Application\UseCase\Army\CreateUnit\CreateUnitRequest;
use App\Application\UseCase\Army\CreateUnit\CreateUnitUseCase;
use App\Application\UseCase\Army\ListArmyUnits\ListArmyUnitsRequest;
use App\Application\UseCase\Army\ListArmyUnits\ListArmyUnitsUseCase;
use App\Application\UseCase\Army\ListPaintingSessions\ListPaintingSessionsRequest;
use App\Application\UseCase\Army\ListPaintingSessions\ListPaintingSessionsUseCase;
use App\Application\UseCase\Army\RecordPaintingSession\RecordPaintingSessionRequest;
use App\Application\UseCase\Army\RecordPaintingSession\RecordPaintingSessionUseCase;
use App\Application\UseCase\Army\UpdateUnitComponent\UpdateUnitComponentRequest;
use App\Application\UseCase\Army\UpdateUnitComponent\UpdateUnitComponentUseCase;
use App\Application\UseCase\Army\UpdateUnitComponentProgress\UpdateUnitComponentProgressRequest;
use App\Application\UseCase\Army\UpdateUnitComponentProgress\UpdateUnitComponentProgressUseCase;
use App\Application\UseCase\Army\RemoveUnitComponent\RemoveUnitComponentRequest;
use App\Application\UseCase\Army\RemoveUnitComponent\RemoveUnitComponentUseCase;
use App\Application\UseCase\Army\CompleteUnit\CompleteUnitRequest;
use App\Application\UseCase\Army\CompleteUnit\CompleteUnitUseCase;
use App\Application\UseCase\Army\ReorderUnits\ReorderUnitsRequest;
use App\Application\UseCase\Army\ReorderUnits\ReorderUnitsUseCase;
use App\Application\UseCase\Army\RecordMiniaturePainting\RecordMiniaturePaintingRequest;
use App\Application\UseCase\Army\RecordMiniaturePainting\RecordMiniaturePaintingUseCase;
use App\Application\UseCase\Army\UpdateUnitCategory\UpdateUnitCategoryRequest;
use App\Application\UseCase\Army\UpdateUnitCategory\UpdateUnitCategoryUseCase;
use App\Application\UseCase\Army\UpdatePaintingPlan\UpdatePaintingPlanRequest;
use App\Application\UseCase\Army\UpdatePaintingPlan\UpdatePaintingPlanUseCase;
use App\Application\UseCase\Army\UpdatePaintingSession\UpdatePaintingSessionRequest;
use App\Application\UseCase\Army\UpdatePaintingSession\UpdatePaintingSessionUseCase;
use App\Application\UseCase\Army\UpdateUnitFormation\UpdateUnitFormationRequest;
use App\Application\UseCase\Army\UpdateUnitFormation\UpdateUnitFormationUseCase;
use App\Domain\Exception\ValidationException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api')]
final class ProjectUnitController extends ApiController
{
    #[Route('/projects/{projectId}/units', name: 'api_project_units_list', methods: ['GET'])]
    public function listUnits(string $projectId, ListArmyUnitsUseCase $useCase): JsonResponse
    {
        $units = $useCase->execute(new ListArmyUnitsRequest($projectId));
        return new JsonResponse(['units' => array_map(static fn($unit) => $unit->toArray(), $units)]);
    }

    #[Route('/projects/{projectId}/units', name: 'api_project_units_create', methods: ['POST'])]
    public function createUnit(string $projectId, Request $request, CreateUnitUseCase $useCase): JsonResponse
    {
        $data = $this->jsonBody($request, ['name']);
        $unit = $useCase->execute(new CreateUnitRequest(
            $projectId,
            $this->currentUserId()->value(),
            (string) $data['name'],
            (string) ($data['category'] ?? 'infantry'),
            (int) ($data['modelsPerRow'] ?? 5),
        ));
        return new JsonResponse($unit->toArray(), Response::HTTP_CREATED);
    }

    #[Route('/projects/{projectId}/units/order', name: 'api_project_units_order', methods: ['PUT'])]
    public function reorderUnits(string $projectId, Request $request, ReorderUnitsUseCase $useCase): JsonResponse
    {
        $data = $this->jsonBody($request, ['unitIds']);
        if (!is_array($data['unitIds']) || !array_is_list($data['unitIds'])) {
            throw new ValidationException('unitIds debe ser una lista de identificadores');
        }
        $units = $useCase->execute(new ReorderUnitsRequest($projectId, $data['unitIds']));
        return new JsonResponse(['units' => array_map(static fn($unit) => UnitDTO::fromEntity($unit)->toArray(), $units)]);
    }

    #[Route('/units/{id}/category', name: 'api_unit_category_update', methods: ['PUT'])]
    public function updateUnitCategory(string $id, Request $request, UpdateUnitCategoryUseCase $useCase): JsonResponse
    {
        $data = $this->jsonBody($request, ['category']);
        $unit = $useCase->execute(new UpdateUnitCategoryRequest($id, (string) $data['category']));
        return new JsonResponse($unit->toArray());
    }

    #[Route('/units/{id}/formation', name: 'api_unit_formation_update', methods: ['PUT'])]
    public function updateUnitFormation(string $id, Request $request, UpdateUnitFormationUseCase $useCase): JsonResponse
    {
        $data = $this->jsonBody($request, ['modelsPerRow']);
        $unit = $useCase->execute(new UpdateUnitFormationRequest($id, (int) $data['modelsPerRow']));
        return new JsonResponse($unit->toArray());
    }

    #[Route('/units/{id}/complete', name: 'api_unit_complete', methods: ['POST'])]
    public function completeUnit(string $id, CompleteUnitUseCase $useCase): JsonResponse
    {
        $unit = $useCase->execute(new CompleteUnitRequest($id));
        return new JsonResponse($unit->toArray());
    }

    #[Route('/unit-components/{id}/paint', name: 'api_miniature_paint_manual', methods: ['POST'])]
    public function recordMiniaturePainting(string $id, Request $request, RecordMiniaturePaintingUseCase $useCase): JsonResponse
    {
        $data = $this->jsonBody($request, ['durationSeconds']);
        $painting = $useCase->execute(new RecordMiniaturePaintingRequest(
            $id,
            $this->currentUserId()->value(),
            (int) $data['durationSeconds'],
        ));
        return new JsonResponse($painting->toArray(), Response::HTTP_CREATED);
    }

    #[Route('/units/{unitId}/components', name: 'api_unit_components_create', methods: ['POST'])]
    public function addUnitComponent(string $unitId, Request $request, AddUnitComponentUseCase $useCase): JsonResponse
    {
        $data = $this->jsonBody($request, ['label', 'quantityTotal']);
        $component = $useCase->execute(new AddUnitComponentRequest($unitId, $this->currentUserId()->value(), (string) $data['label'], (int) $data['quantityTotal']));
        return new JsonResponse($component->toArray(), Response::HTTP_CREATED);
    }

    #[Route('/unit-components/{id}', name: 'api_unit_components_update', methods: ['PUT'])]
    public function updateUnitComponent(string $id, Request $request, UpdateUnitComponentUseCase $useCase): JsonResponse
    {
        $data = $this->jsonBody($request, ['label', 'quantityTotal']);
        $component = $useCase->execute(new UpdateUnitComponentRequest($id, (string) $data['label'], (int) $data['quantityTotal']));
        return new JsonResponse($component->toArray());
    }

    #[Route('/unit-components/{id}/progress', name: 'api_unit_components_progress', methods: ['PUT'])]
    public function updateUnitComponentProgress(string $id, Request $request, UpdateUnitComponentProgressUseCase $useCase): JsonResponse
    {
        $data = $this->jsonBody($request, ['delta']);
        $component = $useCase->execute(new UpdateUnitComponentProgressRequest($id, (int) $data['delta']));
        return new JsonResponse($component->toArray());
    }

    #[Route('/unit-components/{id}', name: 'api_unit_components_delete', methods: ['DELETE'])]
    public function removeUnitComponent(string $id, RemoveUnitComponentUseCase $useCase): Response
    {
        $useCase->execute(new RemoveUnitComponentRequest($id));
        return new Response(status: Response::HTTP_NO_CONTENT);
    }

    #[Route('/units/{id}/painting-plan', name: 'api_painting_plans_create', methods: ['POST'])]
    public function createPaintingPlan(string $id, Request $request, CreatePaintingPlanUseCase $useCase): JsonResponse
    {
        $data = $this->jsonBody($request, ['estimatedHours']);
        $plan = $useCase->execute(new CreatePaintingPlanRequest($id, $this->currentUserId()->value(), (float) $data['estimatedHours']));
        return new JsonResponse($plan->toArray(), Response::HTTP_CREATED);
    }

    #[Route('/painting-plans/{id}', name: 'api_painting_plans_update', methods: ['PUT'])]
    public function updatePaintingPlan(string $id, Request $request, UpdatePaintingPlanUseCase $useCase): JsonResponse
    {
        $data = $this->jsonBody($request, ['estimatedHours']);
        $plan = $useCase->execute(new UpdatePaintingPlanRequest(
            $id,
            $this->currentUserId()->value(),
            (float) $data['estimatedHours'],
        ));
        return new JsonResponse($plan->toArray());
    }

    #[Route('/painting-plans/{id}/sessions', name: 'api_painting_sessions_create', methods: ['POST'])]
    public function recordPaintingSession(string $id, Request $request, RecordPaintingSessionUseCase $useCase): JsonResponse
    {
        $data = $this->jsonBody($request, ['durationSeconds']);
        $session = $useCase->execute(new RecordPaintingSessionRequest($id, $this->currentUserId()->value(), (int) $data['durationSeconds']));
        return new JsonResponse($session->toArray(), Response::HTTP_CREATED);
    }

    #[Route('/painting-plans/{id}/sessions', name: 'api_painting_sessions_list', methods: ['GET'])]
    public function listPaintingSessions(string $id, ListPaintingSessionsUseCase $useCase): JsonResponse
    {
        $sessions = $useCase->execute(new ListPaintingSessionsRequest($id));
        return new JsonResponse(['sessions' => array_map(static fn($session) => $session->toArray(), $sessions)]);
    }

    #[Route('/painting-sessions/{id}', name: 'api_painting_sessions_update', methods: ['PUT'])]
    public function updatePaintingSession(string $id, Request $request, UpdatePaintingSessionUseCase $useCase): JsonResponse
    {
        $data = $this->jsonBody($request, ['durationSeconds']);
        $session = $useCase->execute(new UpdatePaintingSessionRequest($id, (int) $data['durationSeconds']));
        return new JsonResponse($session->toArray());
    }
}
