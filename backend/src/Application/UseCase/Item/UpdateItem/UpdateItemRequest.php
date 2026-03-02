<?php

declare(strict_types=1);

namespace App\Application\UseCase\Item\UpdateItem;

use App\Domain\ValueObject\ItemId;
use DateTimeImmutable;

final class UpdateItemRequest
{
    public function __construct(
        private readonly string  $itemId,
        private readonly string  $name,
        private readonly float  $estimatedHours,
    ) {}

    public function itemId(): ItemId
    {
        return ItemId::fromString($this->itemId);
    }
    
    public function name(): string
    {
        return $this->name;
    }

    public function estimatedHours(): float
    {
        return $this->estimatedHours;
    }
}