<?php

declare(strict_types=1);

namespace App\Infrastructure\Controller\Api;

use App\Application\UseCase\Army\AddUnitComponent\AddUnitComponentRequest;
use App\Application\UseCase\Army\AddUnitComponent\AddUnitComponentUseCase;
use App\Application\UseCase\Army\CreateArmy\CreateArmyRequest;
use App\Application\UseCase\Army\CreateArmy\CreateArmyUseCase;
use App\Application\UseCase\Army\CreatePaintingPlan\CreatePaintingPlanRequest;
use App\Application\UseCase\Army\CreatePaintingPlan\CreatePaintingPlanUseCase;
use App\Application\UseCase\Army\CreateUnit\CreateUnitRequest;
use App\Application\UseCase\Army\CreateUnit\CreateUnitUseCase;
use App\Application\UseCase\Army\ListArmies\ListArmiesRequest;
use App\Application\UseCase\Army\ListArmies\ListArmiesUseCase;
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
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api')]
final class ArmyController extends ApiController
{
    #[Route('/armies', name: 'api_armies_list', methods: ['GET'])]
    public function listArmies(ListArmiesUseCase $useCase): JsonResponse
    {
        $armies = $useCase->execute(new ListArmiesRequest($this->currentUserId()->value()));
        return new JsonResponse(['armies' => array_map(static fn($army) => $army->toArray(), $armies)]);
    }

    #[Route('/armies', name: 'api_armies_create', methods: ['POST'])]
    public function createArmy(Request $request, CreateArmyUseCase $useCase): JsonResponse
    {
        $data = $this->jsonBody($request, ['name']);
        $army = $useCase->execute(new CreateArmyRequest($this->currentUserId()->value(), (string) $data['name']));
        return new JsonResponse($army->toArray(), Response::HTTP_CREATED);
    }

    #[Route('/armies/{armyId}/units', name: 'api_army_units_list', methods: ['GET'])]
    public function listUnits(string $armyId, ListArmyUnitsUseCase $useCase): JsonResponse
    {
        $units = $useCase->execute(new ListArmyUnitsRequest($armyId));
        return new JsonResponse(['units' => array_map(static fn($unit) => $unit->toArray(), $units)]);
    }

    #[Route('/armies/{armyId}/units', name: 'api_army_units_create', methods: ['POST'])]
    public function createUnit(string $armyId, Request $request, CreateUnitUseCase $useCase): JsonResponse
    {
        $data = $this->jsonBody($request, ['name']);
        $unit = $useCase->execute(new CreateUnitRequest($armyId, $this->currentUserId()->value(), (string) $data['name']));
        return new JsonResponse($unit->toArray(), Response::HTTP_CREATED);
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
}
