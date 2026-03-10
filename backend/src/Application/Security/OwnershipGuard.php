<?php

declare(strict_types=1);

namespace App\Application\Security;

use App\Application\Port\CurrentUserProvider;
use App\Domain\Exception\AccessDeniedException;
use App\Domain\Security\OwnableResource;

final class OwnershipGuard
{
    public function __construct(
        private readonly CurrentUserProvider $currentUserProvider,
    ) {}

    public function ensureOwnership(OwnableResource $resource): void
    {
        $currentUserId = $this->currentUserProvider->currentUserId();

        if (!$resource->ownerId()->equals($currentUserId)) {
            throw new AccessDeniedException();
        }
    }
}
