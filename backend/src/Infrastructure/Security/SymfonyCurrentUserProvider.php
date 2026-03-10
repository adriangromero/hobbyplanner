<?php

declare(strict_types=1);

namespace App\Infrastructure\Security;

use App\Application\Port\CurrentUserProvider;
use App\Domain\Repository\UserRepositoryInterface;
use App\Domain\ValueObject\Email;
use App\Domain\ValueObject\UserId;
use Symfony\Bundle\SecurityBundle\Security;

final class SymfonyCurrentUserProvider implements CurrentUserProvider
{
    public function __construct(
        private readonly Security                $security,
        private readonly UserRepositoryInterface $userRepository,
    ) {}

    public function currentUserId(): UserId
    {
        $securityUser = $this->security->getUser();

        if ($securityUser === null) {
            throw new \RuntimeException('No authenticated user');
        }

        $user = $this->userRepository->findByEmail(
            Email::fromString($securityUser->getUserIdentifier())
        );

        if ($user === null) {
            throw new \RuntimeException('Authenticated user not found in database');
        }

        return $user->id();
    }
}
