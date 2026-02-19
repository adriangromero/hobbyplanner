<?php

namespace App\Domain\ValueObject;

class ItemPhases
{
    private bool $assemblyCompleted = false;
    private bool $primingCompleted = false;
    private bool $basecoatCompleted = false;
    private bool $shadingCompleted = false;
    private bool $highlightingCompleted = false;
    private bool $detailsCompleted = false;
    private bool $baseWorkCompleted = false;
    private bool $varnishCompleted = false;
    
    public function complete(Phase $phase): void
    {
        match($phase) {
            Phase::ASSEMBLY => $this->assemblyCompleted = true,
            Phase::PRIMING => $this->primingCompleted = true,
            Phase::BASECOAT => $this->basecoatCompleted = true,
            Phase::SHADING => $this->shadingCompleted = true,
            Phase::HIGHLIGHTING => $this->highlightingCompleted = true,
            Phase::DETAILS => $this->detailsCompleted = true,
            Phase::BASE_WORK => $this->baseWorkCompleted = true,
            Phase::VARNISH => $this->varnishCompleted = true,
        };
    }
    
    public function uncomplete(Phase $phase): void
    {
        match($phase) {
            Phase::ASSEMBLY => $this->assemblyCompleted = false,
            Phase::PRIMING => $this->primingCompleted = false,
            Phase::BASECOAT => $this->basecoatCompleted = false,
            Phase::SHADING => $this->shadingCompleted = false,
            Phase::HIGHLIGHTING => $this->highlightingCompleted = false,
            Phase::DETAILS => $this->detailsCompleted = false,
            Phase::BASE_WORK => $this->baseWorkCompleted = false,
            Phase::VARNISH => $this->varnishCompleted = false,
        };
    }
    
    public function isCompleted(Phase $phase): bool
    {
        return match($phase) {
            Phase::ASSEMBLY => $this->assemblyCompleted,
            Phase::PRIMING => $this->primingCompleted,
            Phase::BASECOAT => $this->basecoatCompleted,
            Phase::SHADING => $this->shadingCompleted,
            Phase::HIGHLIGHTING => $this->highlightingCompleted,
            Phase::DETAILS => $this->detailsCompleted,
            Phase::BASE_WORK => $this->baseWorkCompleted,
            Phase::VARNISH => $this->varnishCompleted,
        };
    }
    
    public function getCompletedCount(): int
    {
        return array_sum([
            $this->assemblyCompleted ? 1 : 0,
            $this->primingCompleted ? 1 : 0,
            $this->basecoatCompleted ? 1 : 0,
            $this->shadingCompleted ? 1 : 0,
            $this->highlightingCompleted ? 1 : 0,
            $this->detailsCompleted ? 1 : 0,
            $this->baseWorkCompleted ? 1 : 0,
            $this->varnishCompleted ? 1 : 0,
        ]);
    }
    
    public function getProgress(): float
    {
        return ($this->getCompletedCount() / 8) * 100;
    }
    
    public function allCompleted(): bool
    {
        return $this->getCompletedCount() === 8;
    }
}

enum Phase: string
{
    case ASSEMBLY = 'ASSEMBLY';
    case PRIMING = 'PRIMING';
    case BASECOAT = 'BASECOAT';
    case SHADING = 'SHADING';
    case HIGHLIGHTING = 'HIGHLIGHTING';
    case DETAILS = 'DETAILS';
    case BASE_WORK = 'BASE_WORK';
    case VARNISH = 'VARNISH';
    
    public function getLabel(): string
    {
        return match($this) {
            self::ASSEMBLY => 'Assembly',
            self::PRIMING => 'Priming',
            self::BASECOAT => 'Basecoat',
            self::SHADING => 'Shading',
            self::HIGHLIGHTING => 'Highlighting',
            self::DETAILS => 'Details',
            self::BASE_WORK => 'Base Work',
            self::VARNISH => 'Varnish',
        };
    }
}