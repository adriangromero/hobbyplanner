<?php

declare(strict_types=1);

namespace App\Application\Port;

use App\Domain\Entity\User;

interface TokenGeneratorPort
{
    public function generate(User $user): string;
}
