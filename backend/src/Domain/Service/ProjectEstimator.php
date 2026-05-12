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
     * @param Item[]        $items
     * @param WorkSession[] $sessions
     */
    public function estimate(
        DateTimeImmutable $projectStart,
        array $items,
        array $sessions,
    ): ProjectEstimation {
        $closedSessions = $this->closedSessions($sessions);
        $totalWorkedHours = $this->sumDuration($closedSessions);

        $pendingItems = array_filter($items, fn(Item $i) => !$i->status()->isCompleted());

        $totalEstimatedHours   = $this->sumEstimated($items);
        $pendingEstimatedHours = $this->sumEstimated($pendingItems);
        $pendingWorkedHours    = $this->workedHoursForItems($closedSessions, $pendingItems);
        $remainingHours        = max(0.0, $pendingEstimatedHours - $pendingWorkedHours);

        $activeDays       = $this->countActiveDays($closedSessions);
        $today            = new DateTimeImmutable();
        $daysSinceStart   = max(1, $projectStart->diff($today)->days);
        $weeksSinceStart  = max(1.0, $daysSinceStart / 7);

        $velocityPerActiveDay = $activeDays > 0
            ? $totalWorkedHours / $activeDays
            : 0.0;

        $frequencyDaysPerWeek = $activeDays / $weeksSinceStart;

        [$activeDaysRemaining, $daysRemaining, $completionDate] = $this->projectCompletion(
            $remainingHours,
            $velocityPerActiveDay,
            $frequencyDaysPerWeek,
            $today,
        );

        return ProjectEstimation::create(
            startDate:               $projectStart,
            estimatedHours:          $totalEstimatedHours,
            workedHours:             $totalWorkedHours,
            remainingHours:          $remainingHours,
            velocityPerActiveDay:    $velocityPerActiveDay,
            activeDays:              $activeDays,
            frequencyDaysPerWeek:    round($frequencyDaysPerWeek, 1),
            activeDaysRemaining:     $activeDaysRemaining,
            daysRemaining:           $daysRemaining,
            estimatedCompletionDate: $completionDate,
        );
    }

    /**
     * @param Item[] $items
     */
    private function sumEstimated(array $items): float
    {
        return array_sum(array_map(fn(Item $i) => $i->estimatedHours(), $items));
    }

    /**
     * @param WorkSession[] $sessions
     */
    private function sumDuration(array $sessions): float
    {
        return array_sum(array_map(fn(WorkSession $s) => $s->durationHours(), $sessions));
    }

    /**
     * Sum worked hours only for sessions belonging to the given items.
     *
     * @param WorkSession[] $closedSessions
     * @param Item[]        $items
     */
    private function workedHoursForItems(array $closedSessions, array $items): float
    {
        $itemIds = [];
        foreach ($items as $item) {
            $itemIds[$item->id()->value()] = true;
        }

        $hours = 0.0;
        foreach ($closedSessions as $session) {
            if (isset($itemIds[$session->itemId()->value()])) {
                $hours += $session->durationHours();
            }
        }

        return $hours;
    }

    /**
     * @param WorkSession[] $sessions
     * @return WorkSession[]
     */
    private function closedSessions(array $sessions): array
    {
        return array_filter($sessions, fn(WorkSession $s) => $s->endedAt() !== null);
    }

    /**
     * @param WorkSession[] $closedSessions
     */
    private function countActiveDays(array $closedSessions): int
    {
        $days = [];

        foreach ($closedSessions as $session) {
            $days[$session->workedDay()] = true;
        }

        return count($days);
    }

    /**
     * @return array{?int, ?int, ?DateTimeImmutable}
     */
    private function projectCompletion(
        float             $remainingHours,
        float             $velocityPerActiveDay,
        float             $frequencyDaysPerWeek,
        DateTimeImmutable $today,
    ): array {
        if ($velocityPerActiveDay <= 0 || $frequencyDaysPerWeek <= 0) {
            return [null, null, null];
        }

        $activeDaysRemaining = (int) ceil($remainingHours / $velocityPerActiveDay);
        $daysRemaining       = (int) ceil($activeDaysRemaining * 7 / $frequencyDaysPerWeek);
        $completionDate      = $today->modify("+{$daysRemaining} days");

        return [$activeDaysRemaining, $daysRemaining, $completionDate];
    }
}
