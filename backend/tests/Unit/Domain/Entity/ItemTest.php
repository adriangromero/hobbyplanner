<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\Entity;

use App\Domain\Entity\Item;
use App\Domain\Exception\ValidationException;
use App\Domain\ValueObject\ItemId;
use App\Domain\ValueObject\ProjectId;
use App\Domain\ValueObject\UserId;
use PHPUnit\Framework\TestCase;

final class ItemTest extends TestCase
{
    public function testConstruct(): void
    {
        $item = $this->createItem();

        $this->assertSame('Test Item', $item->name());
        $this->assertSame(5.0, $item->estimatedHours());
    }

    public function testConstructEmptyNameThrows(): void
    {
        $this->expectException(ValidationException::class);

        new Item(ItemId::create(), ProjectId::create(), UserId::create(), '   ', 5.0);
    }

    public function testConstructZeroHoursThrows(): void
    {
        $this->expectException(ValidationException::class);

        new Item(ItemId::create(), ProjectId::create(), UserId::create(), 'Item', 0);
    }

    public function testConstructNegativeHoursThrows(): void
    {
        $this->expectException(ValidationException::class);

        new Item(ItemId::create(), ProjectId::create(), UserId::create(), 'Item', -1.0);
    }

    public function testUpdate(): void
    {
        $item = $this->createItem();
        $item->update('New Name', 10.0);

        $this->assertSame('New Name', $item->name());
        $this->assertSame(10.0, $item->estimatedHours());
    }

    public function testRename(): void
    {
        $item = $this->createItem();
        $item->rename('Renamed');

        $this->assertSame('Renamed', $item->name());
    }

    public function testRenameTrimsWhitespace(): void
    {
        $item = $this->createItem();
        $item->rename('  Trimmed  ');

        $this->assertSame('Trimmed', $item->name());
    }

    public function testRenameEmptyThrows(): void
    {
        $item = $this->createItem();

        $this->expectException(ValidationException::class);
        $item->rename('');
    }

    public function testUpdateEstimatedHoursZeroThrows(): void
    {
        $item = $this->createItem();

        $this->expectException(ValidationException::class);
        $item->updateEstimatedHours(0);
    }

    public function testTimestampsAreSet(): void
    {
        $item = $this->createItem();

        $this->assertNotNull($item->createdAt());
        $this->assertNotNull($item->updatedAt());
    }

    private function createItem(): Item
    {
        return new Item(
            ItemId::create(),
            ProjectId::create(),
            UserId::create(),
            'Test Item',
            5.0,
        );
    }
}
