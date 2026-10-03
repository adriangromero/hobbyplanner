<?php

declare(strict_types=1);

namespace App\Tests\Unit\Application\UseCase;

use App\Application\Port\CurrentUserProvider;
use App\Application\Security\OwnershipGuard;
use App\Application\UseCase\Item\ToggleItemStatus\ToggleItemStatusRequest;
use App\Application\UseCase\Item\ToggleItemStatus\ToggleItemStatusUseCase;
use App\Domain\Entity\Item;
use App\Domain\Repository\ItemRepositoryInterface;
use App\Domain\ValueObject\ProjectId;
use App\Domain\ValueObject\UserId;
use PHPUnit\Framework\TestCase;

final class ToggleItemStatusUseCaseTest extends TestCase
{
    public function testAdvancesPendingThroughInProgressAndCompletedThenReactivates(): void
    {
        $userId = UserId::create();
        $item = Item::create(ProjectId::create(), $userId, 'Escuadra', 4.0);
        $repo = $this->createMock(ItemRepositoryInterface::class);
        $repo->method('findById')->willReturn($item);
        $repo->expects(self::exactly(3))->method('save');
        $users = $this->createMock(CurrentUserProvider::class);
        $users->method('currentUserId')->willReturn($userId);
        $useCase = new ToggleItemStatusUseCase($repo, new OwnershipGuard($users));
        $request = new ToggleItemStatusRequest($item->id()->value());

        self::assertSame('in_progress', $useCase->execute($request)->item()->status);
        self::assertSame('completed', $useCase->execute($request)->item()->status);
        self::assertSame('pending', $useCase->execute($request)->item()->status);
    }
}
