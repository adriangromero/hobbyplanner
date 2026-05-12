<?php

declare(strict_types=1);

namespace App\Tests\Unit\Application\UseCase;

use App\Application\Port\CurrentUserProvider;
use App\Application\Port\TransactionPort;
use App\Application\Security\OwnershipGuard;
use App\Application\UseCase\Item\DeleteItem\DeleteItemRequest;
use App\Application\UseCase\Item\DeleteItem\DeleteItemUseCase;
use App\Domain\Entity\Item;
use App\Domain\Exception\AccessDeniedException;
use App\Domain\Exception\ItemNotFoundException;
use App\Domain\Repository\ItemRepositoryInterface;
use App\Domain\Repository\WorkSessionRepositoryInterface;
use App\Domain\ValueObject\ItemId;
use App\Domain\ValueObject\ProjectId;
use App\Domain\ValueObject\UserId;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

final class DeleteItemUseCaseTest extends TestCase
{
    private ItemRepositoryInterface&MockObject $itemRepository;
    private WorkSessionRepositoryInterface&MockObject $sessionRepository;
    private CurrentUserProvider&MockObject $currentUserProvider;
    private DeleteItemUseCase $useCase;

    protected function setUp(): void
    {
        $this->itemRepository      = $this->createMock(ItemRepositoryInterface::class);
        $this->sessionRepository   = $this->createMock(WorkSessionRepositoryInterface::class);
        $this->currentUserProvider = $this->createMock(CurrentUserProvider::class);

        $transaction = $this->createMock(TransactionPort::class);
        $transaction->method('transactional')->willReturnCallback(fn(callable $op) => $op());

        $this->useCase = new DeleteItemUseCase(
            $this->itemRepository,
            $this->sessionRepository,
            new OwnershipGuard($this->currentUserProvider),
            $transaction,
        );
    }

    public function testDeletesSessionsBeforeItem(): void
    {
        $userId = UserId::create();
        $item   = Item::create(ProjectId::create(), $userId, 'Item', 5.0);

        $this->currentUserProvider->method('currentUserId')->willReturn($userId);
        $this->itemRepository->method('findById')->willReturn($item);

        $callOrder = [];

        $this->sessionRepository
            ->expects($this->once())
            ->method('deleteByItem')
            ->willReturnCallback(function () use (&$callOrder) { $callOrder[] = 'deleteByItem'; });

        $this->itemRepository
            ->expects($this->once())
            ->method('delete')
            ->willReturnCallback(function () use (&$callOrder) { $callOrder[] = 'deleteItem'; });

        $this->useCase->execute(new DeleteItemRequest($item->id()->value()));

        $this->assertSame(['deleteByItem', 'deleteItem'], $callOrder);
    }

    public function testItemNotFoundThrows(): void
    {
        $this->itemRepository->method('findById')->willReturn(null);

        $this->expectException(ItemNotFoundException::class);

        $this->useCase->execute(new DeleteItemRequest(ItemId::create()->value()));
    }

    public function testDeniesAccessWhenUserDoesNotOwnItem(): void
    {
        $ownerId = UserId::create();
        $otherId = UserId::create();
        $item    = Item::create(ProjectId::create(), $ownerId, 'Item', 5.0);

        $this->currentUserProvider->method('currentUserId')->willReturn($otherId);
        $this->itemRepository->method('findById')->willReturn($item);

        $this->expectException(AccessDeniedException::class);

        $this->useCase->execute(new DeleteItemRequest($item->id()->value()));
    }
}
