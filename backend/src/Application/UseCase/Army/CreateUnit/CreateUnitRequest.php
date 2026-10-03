<?php

declare(strict_types=1);

namespace App\Application\UseCase\Army\CreateUnit;

use App\Domain\ValueObject\ArmyId;
use App\Domain\ValueObject\UserId;

final class CreateUnitRequest
{
    public function __construct(private readonly string $armyId, private readonly string $userId, private readonly string $name) {}
    public function armyId(): ArmyId { return ArmyId::fromString($this->armyId); }
    public function userId(): UserId { return UserId::fromString($this->userId); }
    public function name(): string { return $this->name; }
}
