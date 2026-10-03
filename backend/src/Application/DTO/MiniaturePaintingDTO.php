<?php

declare(strict_types=1);

namespace App\Application\DTO;

final class MiniaturePaintingDTO
{
    public function __construct(
        private readonly UnitComponentDTO $component,
        private readonly PaintingSessionDTO $session,
    ) {}

    public function toArray(): array
    {
        return ['component' => $this->component->toArray(), 'session' => $this->session->toArray()];
    }
}
