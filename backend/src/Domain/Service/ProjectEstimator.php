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
        $estimatedHours = array_sum(array_map(fn(Item $i) => $i->estimatedHours(), $items));
        $workedHours    = array_sum(array_map(fn(WorkSession $s) => $s->durationHours(), $sessions));
        $remainingHours = max(0.0, $estimatedHours - $workedHours);

        $activeDays = $this->countActiveDays($sessions);
        $today      = new DateTimeImmutable();

        $daysSinceStart       = max(1, $projectStart->diff($today)->days);
        $weeksSinceStart      = max(1.0, $daysSinceStart / 7);
        $velocityPerActiveDay = $activeDays > 0 ? $workedHours / $activeDays : 0.0;
        $frequencyDaysPerWeek = $activeDays / $weeksSinceStart;

        $activeDaysRemaining      = null;
        $daysRemaining            = null;
        $estimatedCompletionDate  = null;

        if ($velocityPerActiveDay > 0 && $frequencyDaysPerWeek > 0) {
            $activeDaysRemaining = (int) ceil($remainingHours / $velocityPerActiveDay);

            // Convertir días activos a días reales de calendario
            $daysRemaining = (int) ceil($activeDaysRemaining * 7 / $frequencyDaysPerWeek);

            $estimatedCompletionDate = $today->modify("+{$daysRemaining} days");
        }

        return new ProjectEstimation(
            startDate:                $projectStart,
            estimatedHours:           $estimatedHours,
            workedHours:              $workedHours,
            remainingHours:           $remainingHours,
            velocityPerActiveDay:     $velocityPerActiveDay,
            activeDays:               $activeDays,
            frequencyDaysPerWeek:     round($frequencyDaysPerWeek, 1),
            activeDaysRemaining:      $activeDaysRemaining,
            daysRemaining:            $daysRemaining,
            estimatedCompletionDate:  $estimatedCompletionDate,
        );
    }

    /**
     * @param WorkSession[] $sessions
     */
    private function countActiveDays(array $sessions): int
    {
        $days = [];

        foreach ($sessions as $session) {
            if ($session->endedAt() === null) {
                continue;
            }
            $days[$session->workedDay()] = true;
        }

        return count($days);
    }
}
