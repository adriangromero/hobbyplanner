<?php

declare(strict_types=1);

namespace App\Application\UseCase\Item\GetItemSessions;

use App\Application\DTO\WorkSessionDTO;

final class GetItemSessionsResponse
{
    /** @param WorkSessionDTO[] $sessions */
    public function __construct(private readonly array $sessions) {}

    /** @return WorkSessionDTO[] */
    public function sessions(): array
    {
        return $this->sessions;
    }
}
