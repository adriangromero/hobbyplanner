<?php

declare(strict_types=1);

namespace App\Infrastructure\Security;

use App\Application\Port\TokenGeneratorPort;
use App\Domain\Entity\User;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;

final class LexikTokenGenerator implements TokenGeneratorPort
{
    public function __construct(
        private readonly JWTTokenManagerInterface $jwtManager,
    ) {}

    public function generate(User $user): string
    {
        return $this->jwtManager->create(new SecurityUser($user));
    }
}
