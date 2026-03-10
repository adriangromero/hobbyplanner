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
        $item = $this->createItem(10.0);

        $estimation = $this->estimator->estimate(
            new DateTimeImmutable('-7 days'),
            [$item],
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
        $item     = $this->createItem(10.0);
        $sessions = [
            $this->sessionForItem($item, '2026-03-01 10:00:00', '2026-03-01 12:00:00'),
        ];

        $estimation = $this->estimator->estimate(
            new DateTimeImmutable('-7 days'),
            [$item],
            $sessions,
        );

        $this->assertSame(10.0, $estimation->estimatedHours());
        $this->assertSame(2.0, $estimation->workedHours());
        $this->assertSame(8.0, $estimation->remainingHours());
        $this->assertSame(2.0, $estimation->velocityPerActiveDay());
        $this->assertSame(1, $estimation->activeDays());
        $this->assertNotNull($estimation->estimatedCompletionDate());
    }

    public function testMultipleDaysSessions(): void
    {
        $item     = $this->createItem(20.0);
        $sessions = [
            $this->sessionForItem($item, '2026-02-20 10:00:00', '2026-02-20 14:00:00'),
            $this->sessionForItem($item, '2026-02-22 09:00:00', '2026-02-22 13:00:00'),
            $this->sessionForItem($item, '2026-02-24 10:00:00', '2026-02-24 12:00:00'),
        ];

        $estimation = $this->estimator->estimate(
            new DateTimeImmutable('-14 days'),
            [$item],
            $sessions,
        );

        $this->assertSame(20.0, $estimation->estimatedHours());
        $this->assertEqualsWithDelta(10.0, $estimation->workedHours(), 0.01);
        $this->assertEqualsWithDelta(10.0, $estimation->remainingHours(), 0.01);
        $this->assertSame(3, $estimation->activeDays());
    }

    public function testWorkedMoreThanEstimatedClamsToZeroRemaining(): void
    {
        $item     = $this->createItem(2.0);
        $sessions = [
            $this->sessionForItem($item, '2026-03-01 10:00:00', '2026-03-01 13:00:00'),
        ];

        $estimation = $this->estimator->estimate(
            new DateTimeImmutable('-7 days'),
            [$item],
            $sessions,
        );

        $this->assertSame(0.0, $estimation->remainingHours());
    }

    public function testOpenSessionIsIgnored(): void
    {
        $item = $this->createItem(10.0);
        $openSession = new WorkSession(
            WorkSessionId::create(),
            ProjectId::create(),
            $item->id(),
            UserId::create(),
            new DateTimeImmutable(),
            null,
        );

        $estimation = $this->estimator->estimate(
            new DateTimeImmutable('-7 days'),
            [$item],
            [$openSession],
        );

        $this->assertSame(0.0, $estimation->workedHours());
        $this->assertSame(0, $estimation->activeDays());
    }

    public function testCompletedItemsDoNotCountAsRemaining(): void
    {
        // 2 items: pendingItem (8h) + completedItem (5h)
        // 2h worked on pending, 3h worked on completed
        $pendingItem   = $this->createItem(8.0);
        $completedItem = $this->createItem(5.0);
        $completedItem->markAsCompleted();

        $sessions = [
            $this->sessionForItem($pendingItem,   '2026-03-01 10:00:00', '2026-03-01 12:00:00'), // 2h
            $this->sessionForItem($completedItem, '2026-03-02 10:00:00', '2026-03-02 13:00:00'), // 3h
        ];

        $estimation = $this->estimator->estimate(
            new DateTimeImmutable('-14 days'),
            [$pendingItem, $completedItem],
            $sessions,
        );

        // Total estimated = 8 + 5 = 13h (shows full project scope)
        $this->assertSame(13.0, $estimation->estimatedHours());
        // Total worked = 2 + 3 = 5h (all sessions count for velocity)
        $this->assertSame(5.0, $estimation->workedHours());
        // Remaining = pending estimated (8) - pending worked (2) = 6h
        // NOT 13 - 5 - 5 = 3h (old buggy formula)
        $this->assertSame(6.0, $estimation->remainingHours());
        // Velocity uses total worked hours / active days
        $this->assertEqualsWithDelta(2.5, $estimation->velocityPerActiveDay(), 0.01);
    }

    public function testAllItemsCompletedMeansZeroRemaining(): void
    {
        $item = $this->createItem(10.0);
        $item->markAsCompleted();

        $sessions = [
            $this->sessionForItem($item, '2026-03-01 10:00:00', '2026-03-01 13:00:00'),
        ];

        $estimation = $this->estimator->estimate(
            new DateTimeImmutable('-7 days'),
            [$item],
            $sessions,
        );

        $this->assertSame(10.0, $estimation->estimatedHours());
        $this->assertSame(3.0, $estimation->workedHours());
        $this->assertSame(0.0, $estimation->remainingHours());
    }

    // ── Helpers ──────────────────────────────────────────────

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

    private function sessionForItem(Item $item, string $start, string $end): WorkSession
    {
        return new WorkSession(
            WorkSessionId::create(),
            $item->projectId(),
            $item->id(),
            $item->userId(),
            new DateTimeImmutable($start),
            new DateTimeImmutable($end),
        );
    }
}
