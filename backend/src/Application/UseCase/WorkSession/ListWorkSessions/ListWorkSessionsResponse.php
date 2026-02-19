<?php

declare(strict_types=1);

namespace App\Application\UseCase\WorkSession\ListWorkSessions;

use App\Domain\Entity\WorkSession;

final class ListWorkSessionsResponse
{
    /** @var WorkSession[] */
    private array $workSessions;

    /**
     * @param WorkSession[] $workSessions
     */
    public function __construct(array $workSessions)
    {
        $this->workSessions = $workSessions;
    }

    /**
     * @return WorkSession[]
     */
    public function workSessions(): array
    {
        return $this->workSessions;
    }
}
