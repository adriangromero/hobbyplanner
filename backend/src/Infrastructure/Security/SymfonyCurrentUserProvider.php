<?php

declare(strict_types=1);

namespace App\Infrastructure\Security;

use App\Application\Port\CurrentUserProvider;
use App\Domain\Exception\AuthenticationException;
use App\Domain\ValueObject\UserId;
use Symfony\Bundle\SecurityBundle\Security;

final class SymfonyCurrentUserProvider implements CurrentUserProvider
{
    public function __construct(
        private readonly Security $security,
    ) {}

    public function currentUserId(): UserId
    {
        $user = $this->security->getUser();

        if (!$user instanceof SecurityUser) {
            throw new AuthenticationException('No authenticated user');
        }

        return $user->domainUser()->id();
    }
}
