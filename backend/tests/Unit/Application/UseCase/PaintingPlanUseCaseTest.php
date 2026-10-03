<?php

declare(strict_types=1);

namespace App\Tests\Unit\Application\UseCase;

use App\Application\Port\CurrentUserProvider;
use App\Application\Security\OwnershipGuard;
use App\Application\UseCase\Army\CreatePaintingPlan\CreatePaintingPlanRequest;
use App\Application\UseCase\Army\CreatePaintingPlan\CreatePaintingPlanUseCase;
use App\Application\UseCase\Army\RecordPaintingSession\RecordPaintingSessionRequest;
use App\Application\UseCase\Army\RecordPaintingSession\RecordPaintingSessionUseCase;
use App\Domain\Entity\PaintingPlan;
use App\Domain\Entity\Unit;
use App\Domain\Repository\PaintingPlanRepositoryInterface;
use App\Domain\Repository\PaintingSessionRepositoryInterface;
use App\Domain\Repository\UnitRepositoryInterface;
use App\Domain\ValueObject\ProjectId;
use App\Domain\ValueObject\UserId;
use PHPUnit\Framework\TestCase;

final class PaintingPlanUseCaseTest extends TestCase
{
    public function testCreatesPlanForOwnedUnit(): void
    {
        $userId = UserId::create();
        $unit = Unit::create(ProjectId::create(), $userId, 'Escuadra');
        $units = $this->createMock(UnitRepositoryInterface::class);
        $units->method('findById')->willReturn($unit);
        $plans = $this->createMock(PaintingPlanRepositoryInterface::class);
        $plans->method('findByUnit')->willReturn(null);
        $plans->expects(self::once())->method('save')->with(self::callback(
            static fn(PaintingPlan $plan): bool => $plan->unitId()->equals($unit->id())
        ));
        $users = $this->createMock(CurrentUserProvider::class);
        $users->method('currentUserId')->willReturn($userId);

        $dto = (new CreatePaintingPlanUseCase($units, $plans, new OwnershipGuard($users)))
            ->execute(new CreatePaintingPlanRequest($unit->id()->value(), $userId->value(), 8.0));

        self::assertSame($unit->id()->value(), $dto->toArray()['unitId']);
    }

    public function testRecordsSessionAgainstOwnedPlan(): void
    {
        $userId = UserId::create();
        $unit = Unit::create(ProjectId::create(), $userId, 'Escuadra');
        $plan = PaintingPlan::create($unit->id(), $userId, 8.0);
        $plans = $this->createMock(PaintingPlanRepositoryInterface::class);
        $plans->method('findById')->willReturn($plan);
        $sessions = $this->createMock(PaintingSessionRepositoryInterface::class);
        $sessions->expects(self::once())->method('save')->with(self::callback(
            static fn($session): bool => $session->paintingPlanId()->equals($plan->id()) && $session->durationSeconds() === 1800
        ));
        $users = $this->createMock(CurrentUserProvider::class);
        $users->method('currentUserId')->willReturn($userId);

        $dto = (new RecordPaintingSessionUseCase($plans, $sessions, new OwnershipGuard($users)))
            ->execute(new RecordPaintingSessionRequest($plan->id()->value(), $userId->value(), 1800));

        self::assertSame($plan->id()->value(), $dto->toArray()['paintingPlanId']);
    }
}
