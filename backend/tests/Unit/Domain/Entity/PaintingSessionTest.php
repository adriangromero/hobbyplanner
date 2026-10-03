<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\Entity;

use App\Domain\Entity\PaintingSession;
use App\Domain\Exception\ValidationException;
use App\Domain\ValueObject\PaintingPlanId;
use App\Domain\ValueObject\UserId;
use PHPUnit\Framework\TestCase;

final class PaintingSessionTest extends TestCase
{
    public function testRecordsPositiveDurationAgainstPaintingPlan(): void
    {
        $planId = PaintingPlanId::create();
        $session = PaintingSession::record($planId, UserId::create(), 1800);

        self::assertTrue($session->paintingPlanId()->equals($planId));
        self::assertSame(0.5, $session->durationHours());
    }

    public function testRejectsZeroDuration(): void
    {
        $this->expectException(ValidationException::class);
        PaintingSession::record(PaintingPlanId::create(), UserId::create(), 0);
    }
}
