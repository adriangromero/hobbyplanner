<?php

declare(strict_types=1);

namespace App\Application\Port;

use App\Domain\Entity\User;

interface PasswordHasherPort
{
    public function hash(User $user, string $plainPassword): string;

    public function verify(User $user, string $plainPassword): bool;
}
