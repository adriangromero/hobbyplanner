<?php

declare(strict_types=1);

namespace App\Domain\Service;

use App\Domain\Entity\Item;
use App\Domain\Entity\PaintingPlan;
use App\Domain\Entity\PaintingSession;
use App\Domain\Entity\WorkSession;
use App\Domain\ValueObject\ProjectEstimation;
use DateTimeImmutable;

final class ProjectEstimator
{
    /**
     * @param Item[]        $items
     * @param WorkSession[]    $sessions
     * @param PaintingPlan[]   $paintingPlans
     * @param PaintingSession[] $paintingSessions
     */
    public function estimate(
        DateTimeImmutable $projectStart,
        array $items,
        array $sessions,
        array $paintingPlans = [],
        array $paintingSessions = [],
    ): ProjectEstimation {
        $closedSessions = $this->closedSessions($sessions);
        $totalWorkedHours = $this->sumDuration($closedSessions) + $this->sumPaintingDuration($paintingSessions);

        $pendingItems = array_filter($items, fn(Item $i) => !$i->status()->isCompleted());

        $totalEstimatedHours   = $this->sumEstimated($items) + $this->sumPaintingEstimated($paintingPlans);
        $pendingEstimatedHours = $this->sumEstimated($pendingItems);
        $pendingWorkedHours    = $this->workedHoursForItems($closedSessions, $pendingItems);
        $remainingHours        = max(0.0, $pendingEstimatedHours - $pendingWorkedHours)
            + $this->remainingPaintingHours($paintingPlans, $paintingSessions);

        $activeDays = $this->countActiveDays($closedSessions, $paintingSessions);
        $today      = new DateTimeImmutable();

        // Use actual work history as the frequency window. Project/plan creation
        // can predate the first painting session by months and distort the pace.
        $firstActiveDay = $this->firstActiveDay($closedSessions, $paintingSessions);
        $weeksSinceActivity = $firstActiveDay === null
            ? 0.0
            : max(1.0, ($firstActiveDay->diff($today)->days + 1) / 7);

        $velocityPerActiveDay = $activeDays > 0
            ? $totalWorkedHours / $activeDays
            : 0.0;

        $frequencyDaysPerWeek = $weeksSinceActivity > 0
            ? $activeDays / $weeksSinceActivity
            : 0.0;

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

    /** @param PaintingPlan[] $plans */
    private function sumPaintingEstimated(array $plans): float
    {
        return array_sum(array_map(static fn(PaintingPlan $plan): float => $plan->estimatedHours(), $plans));
    }

    /** @param PaintingSession[] $sessions */
    private function sumPaintingDuration(array $sessions): float
    {
        return array_sum(array_map(static fn(PaintingSession $session): float => $session->durationHours(), $sessions));
    }

    /**
     * @param PaintingPlan[] $plans
     * @param PaintingSession[] $sessions
     */
    private function remainingPaintingHours(array $plans, array $sessions): float
    {
        $workedByPlan = [];
        foreach ($sessions as $session) {
            $id = $session->paintingPlanId()->value();
            $workedByPlan[$id] = ($workedByPlan[$id] ?? 0.0) + $session->durationHours();
        }

        $remaining = 0.0;
        foreach ($plans as $plan) {
            $remaining += max(0.0, $plan->estimatedHours() - ($workedByPlan[$plan->id()->value()] ?? 0.0));
        }

        return $remaining;
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
    private function countActiveDays(array $closedSessions, array $paintingSessions = []): int
    {
        $days = [];

        foreach ($closedSessions as $session) {
            $days[$session->workedDay()] = true;
        }
        foreach ($paintingSessions as $session) {
            $days[$session->workedAt()->format('Y-m-d')] = true;
        }

        return count($days);
    }

    /**
     * @param WorkSession[]     $closedSessions
     * @param PaintingSession[] $paintingSessions
     */
    private function firstActiveDay(array $closedSessions, array $paintingSessions): ?DateTimeImmutable
    {
        $firstDay = null;

        foreach ($closedSessions as $session) {
            $day = $session->workedDay();
            if ($firstDay === null || $day < $firstDay) {
                $firstDay = $day;
            }
        }
        foreach ($paintingSessions as $session) {
            $day = $session->workedAt()->format('Y-m-d');
            if ($firstDay === null || $day < $firstDay) {
                $firstDay = $day;
            }
        }

        return $firstDay === null ? null : new DateTimeImmutable($firstDay);
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
