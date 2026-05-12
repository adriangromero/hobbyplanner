<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\Service;

use App\Domain\Entity\Item;
use App\Domain\Entity\WorkSession;
use App\Domain\Service\ProjectEstimator;
use App\Domain\ValueObject\ProjectId;
use App\Domain\ValueObject\UserId;
use DateTimeImmutable;
use PHPUnit\Framework\TestCase;

final class ProjectEstimatorTest extends TestCase
{
    private ProjectEstimator $estimator;
    private ProjectId $projectId;
    private UserId $userId;

    protected function setUp(): void
    {
        $this->estimator = new ProjectEstimator();
        $this->projectId = ProjectId::create();
        $this->userId    = UserId::create();
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

    public function testWorkedMoreThanEstimatedClampsToZeroRemaining(): void
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

        $openSession = WorkSession::startNow(
            $item->projectId(),
            $item->id(),
            $item->userId(),
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
        $pendingItem   = $this->createItem(8.0);
        $completedItem = $this->createItem(5.0);
        $completedItem->markAsCompleted();

        $sessions = [
            $this->sessionForItem($pendingItem,   '2026-03-01 10:00:00', '2026-03-01 12:00:00'),
            $this->sessionForItem($completedItem, '2026-03-02 10:00:00', '2026-03-02 13:00:00'),
        ];

        $estimation = $this->estimator->estimate(
            new DateTimeImmutable('-14 days'),
            [$pendingItem, $completedItem],
            $sessions,
        );

        $this->assertSame(13.0, $estimation->estimatedHours());
        $this->assertSame(5.0, $estimation->workedHours());
        $this->assertSame(6.0, $estimation->remainingHours());
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
        return Item::create(
            $this->projectId,
            $this->userId,
            'Test Item',
            $hours,
        );
    }

    private function sessionForItem(Item $item, string $start, string $end): WorkSession
    {
        $session = WorkSession::startNow(
            $item->projectId(),
            $item->id(),
            $item->userId(),
        );
        $session->update(new DateTimeImmutable($start), new DateTimeImmutable($end));

        return $session;
    }
}
