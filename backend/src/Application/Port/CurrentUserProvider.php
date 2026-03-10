<?php

declare(strict_types=1);

namespace App\Application\Port;

use App\Domain\ValueObject\UserId;

interface CurrentUserProvider
{
    public function currentUserId(): UserId;
}
