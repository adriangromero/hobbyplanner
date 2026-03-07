<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\Service;

use App\Domain\Entity\Item;
use App\Domain\Entity\WorkSession;
use App\Domain\Service\ProjectEstimator;
use App\Domain\ValueObject\ItemId;
use App\Domain\ValueObject\ProjectId;
use App\Domain\ValueObject\UserId;
use App\Domain\ValueObject\WorkSessionId;
use DateTimeImmutable;
use PHPUnit\Framework\TestCase;

final class ProjectEstimatorTest extends TestCase
{
    private ProjectEstimator $estimator;

    protected function setUp(): void
    {
        $this->estimator = new ProjectEstimator();
    }

    public function testNoSessionsReturnsZeroVelocity(): void
    {
        $items = [$this->createItem(10.0)];

        $estimation = $this->estimator->estimate(
            new DateTimeImmutable('-7 days'),
            $items,
            [],
        );

        $this->assertSame(10.0, $estimation->estimatedHours());
        $this->assertSame(0.0, $estimation->workedHours());
        $this->assertSame(10.0, $estimation->remainingHours());
        $this->assertSame(0.0, $estimation->velocityPerActiveDay());
        $this->assertSame(0, $estimation->activeDays());
        $this->assertNull($estimation->estimatedCompletionDate());
    }

    public function testSingleDaySession(): void
    {
        $projectStart = new DateTimeImmutable('-7 days');
        $items    = [$this->createItem(10.0)];
        $sessions = [
            $this->createClosedSession('2026-03-01 10:00:00', '2026-03-01 12:00:00'),
        ];

        $estimation = $this->estimator->estimate($projectStart, $items, $sessions);

        $this->assertSame(10.0, $estimation->estimatedHours());
        $this->assertSame(2.0, $estimation->workedHours());
        $this->assertSame(8.0, $estimation->remainingHours());
        $this->assertSame(2.0, $estimation->velocityPerActiveDay());
        $this->assertSame(1, $estimation->activeDays());
        $this->assertNotNull($estimation->estimatedCompletionDate());
    }

    public function testMultipleDaysSessions(): void
    {
        $projectStart = new DateTimeImmutable('-14 days');
        $items    = [$this->createItem(20.0)];
        $sessions = [
            $this->createClosedSession('2026-02-20 10:00:00', '2026-02-20 14:00:00'),
            $this->createClosedSession('2026-02-22 09:00:00', '2026-02-22 13:00:00'),
            $this->createClosedSession('2026-02-24 10:00:00', '2026-02-24 12:00:00'),
        ];

        $estimation = $this->estimator->estimate($projectStart, $items, $sessions);

        $this->assertSame(20.0, $estimation->estimatedHours());
        $this->assertEqualsWithDelta(10.0, $estimation->workedHours(), 0.01);
        $this->assertEqualsWithDelta(10.0, $estimation->remainingHours(), 0.01);
        $this->assertSame(3, $estimation->activeDays());
    }

    public function testCompletedProjectHasZeroRemaining(): void
    {
        $items    = [$this->createItem(2.0)];
        $sessions = [
            $this->createClosedSession('2026-03-01 10:00:00', '2026-03-01 13:00:00'),
        ];

        $estimation = $this->estimator->estimate(
            new DateTimeImmutable('-7 days'),
            $items,
            $sessions,
        );

        $this->assertSame(0.0, $estimation->remainingHours());
    }

    public function testOpenSessionIsIgnored(): void
    {
        $items = [$this->createItem(10.0)];
        $openSession = new WorkSession(
            WorkSessionId::create(),
            ProjectId::create(),
            ItemId::create(),
            UserId::create(),
            new DateTimeImmutable(),
            null,
        );

        $estimation = $this->estimator->estimate(
            new DateTimeImmutable('-7 days'),
            $items,
            [$openSession],
        );

        $this->assertSame(0.0, $estimation->workedHours());
        $this->assertSame(0, $estimation->activeDays());
    }

    private function createItem(float $hours): Item
    {
        return new Item(
            ItemId::create(),
            ProjectId::create(),
            UserId::create(),
            'Test Item',
            $hours,
        );
    }

    private function createClosedSession(string $start, string $end): WorkSession
    {
        return new WorkSession(
            WorkSessionId::create(),
            ProjectId::create(),
            ItemId::create(),
            UserId::create(),
            new DateTimeImmutable($start),
            new DateTimeImmutable($end),
        );
    }
}
