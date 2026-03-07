<?php

declare(strict_types=1);

namespace App\Tests\Unit\Application\UseCase;

use App\Application\UseCase\Item\DeleteItem\DeleteItemRequest;
use App\Application\UseCase\Item\DeleteItem\DeleteItemUseCase;
use App\Domain\Entity\Item;
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
    private DeleteItemUseCase $useCase;

    protected function setUp(): void
    {
        $this->itemRepository    = $this->createMock(ItemRepositoryInterface::class);
        $this->sessionRepository = $this->createMock(WorkSessionRepositoryInterface::class);
        $this->useCase           = new DeleteItemUseCase($this->itemRepository, $this->sessionRepository);
    }

    public function testDeletesSessionsBeforeItem(): void
    {
        $itemId = ItemId::create();
        $item   = new Item($itemId, ProjectId::create(), UserId::create(), 'Item', 5.0);

        $this->itemRepository
            ->method('findById')
            ->willReturn($item);

        $callOrder = [];

        $this->sessionRepository
            ->expects($this->once())
            ->method('deleteByItem')
            ->willReturnCallback(function () use (&$callOrder) { $callOrder[] = 'deleteByItem'; });

        $this->itemRepository
            ->expects($this->once())
            ->method('delete')
            ->willReturnCallback(function () use (&$callOrder) { $callOrder[] = 'deleteItem'; });

        $this->useCase->execute(new DeleteItemRequest($itemId->value()));

        $this->assertSame(['deleteByItem', 'deleteItem'], $callOrder);
    }

    public function testItemNotFoundThrows(): void
    {
        $this->itemRepository
            ->method('findById')
            ->willReturn(null);

        $this->expectException(ItemNotFoundException::class);

        $this->useCase->execute(new DeleteItemRequest(ItemId::create()->value()));
    }
}
