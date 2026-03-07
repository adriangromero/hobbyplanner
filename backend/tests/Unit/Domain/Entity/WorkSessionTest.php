<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\Entity;

use App\Domain\Entity\WorkSession;
use App\Domain\ValueObject\ItemId;
use App\Domain\ValueObject\ProjectId;
use App\Domain\ValueObject\UserId;
use App\Domain\ValueObject\WorkSessionId;
use DateTimeImmutable;
use PHPUnit\Framework\TestCase;

final class WorkSessionTest extends TestCase
{
    public function testFinish(): void
    {
        $session = $this->createOpenSession();

        $this->assertNull($session->endedAt());

        $session->finish();

        $this->assertNotNull($session->endedAt());
    }

    public function testDurationSecondsOpenSessionReturnsZero(): void
    {
        $session = $this->createOpenSession();

        $this->assertSame(0, $session->durationSeconds());
    }

    public function testDurationSecondsClosedSession(): void
    {
        $start = new DateTimeImmutable('2026-01-01 10:00:00');
        $end   = new DateTimeImmutable('2026-01-01 11:30:00');

        $session = new WorkSession(
            WorkSessionId::create(),
            ProjectId::create(),
            ItemId::create(),
            UserId::create(),
            $start,
            $end,
        );

        $this->assertSame(5400, $session->durationSeconds());
    }

    public function testDurationHours(): void
    {
        $start = new DateTimeImmutable('2026-01-01 10:00:00');
        $end   = new DateTimeImmutable('2026-01-01 11:30:00');

        $session = new WorkSession(
            WorkSessionId::create(),
            ProjectId::create(),
            ItemId::create(),
            UserId::create(),
            $start,
            $end,
        );

        $this->assertSame(1.5, $session->durationHours());
    }

    public function testWorkedDayUsesEndedAt(): void
    {
        $start = new DateTimeImmutable('2026-01-01 23:00:00');
        $end   = new DateTimeImmutable('2026-01-02 01:00:00');

        $session = new WorkSession(
            WorkSessionId::create(),
            ProjectId::create(),
            ItemId::create(),
            UserId::create(),
            $start,
            $end,
        );

        $this->assertSame('2026-01-02', $session->workedDay());
    }

    public function testWorkedDayFallsBackToStartedAt(): void
    {
        $session = $this->createOpenSession();

        $this->assertSame((new DateTimeImmutable())->format('Y-m-d'), $session->workedDay());
    }

    public function testUpdate(): void
    {
        $session = $this->createOpenSession();
        $newStart = new DateTimeImmutable('2026-03-01 09:00:00');
        $newEnd   = new DateTimeImmutable('2026-03-01 11:00:00');

        $session->update($newStart, $newEnd);

        $this->assertSame($newStart, $session->startedAt());
        $this->assertSame($newEnd, $session->endedAt());
    }

    private function createOpenSession(): WorkSession
    {
        return new WorkSession(
            WorkSessionId::create(),
            ProjectId::create(),
            ItemId::create(),
            UserId::create(),
            new DateTimeImmutable(),
            null,
        );
    }
}
