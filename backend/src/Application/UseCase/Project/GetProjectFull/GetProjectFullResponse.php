<?php

declare(strict_types=1);

namespace App\Application\UseCase\Project\GetProjectFull;

use App\Domain\Entity\Project;
use App\Domain\Entity\Item;
use App\Domain\Entity\WorkSession;

final class GetProjectFullResponse
{
    public function __construct(
        private Project $project,
        /** @var Item[] */
        private array $items,
        /** @var WorkSession[] */
        private array $sessions
    ) {}

    public function project(): Project
    {
        return $this->project;
    }

    /**
     * @return Item[]
     */
    public function items(): array
    {
        return $this->items;
    }

    /**
     * @return WorkSession[]
     */
    public function sessions(): array
    {
        return $this->sessions;
    }
}
