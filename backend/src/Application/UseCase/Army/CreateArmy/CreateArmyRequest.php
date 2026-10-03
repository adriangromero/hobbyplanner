<?php

declare(strict_types=1);
namespace App\Application\UseCase\Army\CreateArmy;
use App\Domain\ValueObject\UserId;
final class CreateArmyRequest
{
    public function __construct(private readonly string $userId, private readonly string $name) {}
    public function userId(): UserId { return UserId::fromString($this->userId); }
    public function name(): string { return $this->name; }
}
