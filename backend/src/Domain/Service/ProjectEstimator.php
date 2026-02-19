<?php

declare(strict_types=1);

namespace App\Domain\Service;

use App\Domain\Entity\Item;
use App\Domain\Entity\WorkSession;
use App\Domain\ValueObject\ProjectEstimation;
use DateTimeImmutable;

final class ProjectEstimator
{
    /**
     * @param Item[] $items
     * @param WorkSession[] $sessions
     */
    public function estimate(
        DateTimeImmutable $projectStart,
        array $items,
        array $sessions
    ): ProjectEstimation
    {
        $estimatedHours = array_sum(array_map(fn($i) => $i->estimatedHours(), $items));
        $workedHours = array_sum(array_map(fn($s) => $s->durationHours(), $sessions));
        $remainingHours = $estimatedHours - $workedHours;

        $velocity = $this->calculateVelocitySinceStart($projectStart, $sessions);

        $daysRemaining = $velocity > 0 ? ceil($remainingHours / $velocity) : null;

        $estimatedCompletionDate = $daysRemaining
            ? $projectStart->modify("+{$daysRemaining} days")
            : null;

        $daysRemaining = $velocity > 0
            ? (int) ceil($remainingHours / $velocity)
            : null;

        return new ProjectEstimation(
            $projectStart,
            $estimatedHours,
            $workedHours,
            $remainingHours,
            $velocity,
            $daysRemaining,
            $estimatedCompletionDate
        );
    }


    /**
     * @param WorkSession[] $sessions
     */
    private function calculateVelocity(array $sessions): float
    {
        $thirtyDaysAgo = new DateTimeImmutable('-30 days');

        $recent = array_filter($sessions, fn (WorkSession $s) =>
            $s->endedAt() !== null && $s->endedAt() >= $thirtyDaysAgo
        );

        if (!$recent) {
            return 0.0;
        }

        $hoursByDay = [];

        foreach ($recent as $s) {
            $day = $s->workedDay();
            $hoursByDay[$day] = ($hoursByDay[$day] ?? 0) + $s->durationHours();
        }

        return array_sum($hoursByDay) / count($hoursByDay);
    }

    private function calculateVelocitySinceStart(
        DateTimeImmutable $projectStart,
        array $sessions
    ): float
    {
        $today = new DateTimeImmutable();
        $daysSinceStart = $projectStart->diff($today)->days;

        if ($daysSinceStart === 0) {
            return 0.0;
        }

        $totalHours = array_sum(array_map(fn($s) => $s->durationHours(), $sessions));

        return $totalHours / $daysSinceStart;
    }

}
