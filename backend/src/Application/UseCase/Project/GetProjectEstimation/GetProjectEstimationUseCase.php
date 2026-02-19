<?php

namespace App\Application\UseCase\Project\GetProjectEstimation;

use App\Domain\Repository\ProjectRepositoryInterface;
use App\Domain\Repository\WorkSessionRepositoryInterface;
use App\Domain\ValueObject\ProjectId;

final readonly class GetProjectEstimationUseCase
{
    public function __construct(
        private ProjectRepositoryInterface $projectRepository,
        private WorkSessionRepositoryInterface $workSessionRepository
    ) {}
    
    public function execute(ProjectId $projectId): GetProjectEstimationResponse
    {
        $project = $this->projectRepository->findById($projectId);
        var_dump($project); // Debug: Verificar que el proyecto se encuentra correctamente
        exit;

        // Horas trabajadas y estimadas
        $workedHours = $this->workSessionRepository->getTotalHoursByProject($projectId);
        $estimatedHours = $project->estimatedHours();
        $remainingHours = max(0, $estimatedHours - $workedHours);
        
        // Velocidad (horas/día) basada en últimos 30 días
        $velocity = $this->calculateVelocity($projectId);
        
        // Días restantes = horas restantes / velocidad
        $daysRemaining = $velocity > 0 ? ceil($remainingHours / $velocity) : null;
        
        // Fecha estimada de finalización
        $estimatedCompletionDate = $daysRemaining 
            ? (new \DateTimeImmutable())->modify("+{$daysRemaining} days")
            : null;
        
        return new GetProjectEstimationResponse(
            workedHours: $workedHours,
            estimatedHours: $estimatedHours,
            remainingHours: $remainingHours,
            velocityPerDay: $velocity,
            daysRemaining: $daysRemaining,
            estimatedCompletionDate: $estimatedCompletionDate?->format('Y-m-d')
        );
    }
    
    private function calculateVelocity(ProjectId $projectId): float
    {
        $sessions = $this->workSessionRepository->findByProject($projectId);
        
        if (empty($sessions)) return 0.0;
        
        // Filtrar últimos 30 días
        $thirtyDaysAgo = new \DateTimeImmutable('-30 days');
        $recentSessions = array_filter($sessions, fn($s) => $s->workedAt() >= $thirtyDaysAgo);
        
        if (empty($recentSessions)) return 0.0;
        
        // Agrupar por día
        $hoursByDay = [];
        foreach ($recentSessions as $session) {
            $day = $session->workedAt()->format('Y-m-d');
            $hoursByDay[$day] = ($hoursByDay[$day] ?? 0) + $session->hours();
        }
        
        // Promedio: total horas / días trabajados
        $totalHours = array_sum($hoursByDay);
        $daysWorked = count($hoursByDay);
        
        return $totalHours / $daysWorked;
    }
}