<?php

declare(strict_types=1);
namespace App\Application\UseCase\Army\ListArmies;
use App\Domain\ValueObject\UserId;
final class ListArmiesRequest
{
    public function __construct(private readonly string $userId) {}
    public function userId(): UserId { return UserId::fromString($this->userId); }
}
