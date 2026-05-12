<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\Entity;

use App\Domain\Entity\WorkSession;
use App\Domain\Exception\ValidationException;
use App\Domain\ValueObject\ItemId;
use App\Domain\ValueObject\ProjectId;
use App\Domain\ValueObject\UserId;
use DateTimeImmutable;
use PHPUnit\Framework\TestCase;

final class WorkSessionTest extends TestCase
{
    public function testStartNowCreatesOpenSession(): void
    {
        $session = $this->createOpenSession();

        $this->assertNotNull($session->id());
        $this->assertNotNull($session->startedAt());
        $this->assertNull($session->endedAt());
    }

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
        $session = $this->createSessionWithDates(
            new DateTimeImmutable('2026-01-01 10:00:00'),
            new DateTimeImmutable('2026-01-01 11:30:00'),
        );

        $this->assertSame(5400, $session->durationSeconds());
    }

    public function testDurationHours(): void
    {
        $session = $this->createSessionWithDates(
            new DateTimeImmutable('2026-01-01 10:00:00'),
            new DateTimeImmutable('2026-01-01 11:30:00'),
        );

        $this->assertSame(1.5, $session->durationHours());
    }

    public function testWorkedDayUsesEndedAt(): void
    {
        $session = $this->createSessionWithDates(
            new DateTimeImmutable('2026-01-01 23:00:00'),
            new DateTimeImmutable('2026-01-02 01:00:00'),
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

    public function testUpdateEndBeforeStartThrows(): void
    {
        $session = $this->createOpenSession();

        $this->expectException(ValidationException::class);
        $session->update(
            new DateTimeImmutable('2026-03-01 12:00:00'),
            new DateTimeImmutable('2026-03-01 09:00:00'),
        );
    }

    private function createOpenSession(): WorkSession
    {
        return WorkSession::startNow(
            ProjectId::create(),
            ItemId::create(),
            UserId::create(),
        );
    }

    private function createSessionWithDates(DateTimeImmutable $start, ?DateTimeImmutable $end): WorkSession
    {
        $session = WorkSession::startNow(
            ProjectId::create(),
            ItemId::create(),
            UserId::create(),
        );
        $session->update($start, $end);

        return $session;
    }
}
