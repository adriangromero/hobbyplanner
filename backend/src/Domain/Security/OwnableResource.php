<?php

declare(strict_types=1);

namespace App\Domain\Security;

use App\Domain\ValueObject\UserId;

interface OwnableResource
{
    public function ownerId(): UserId;
}
