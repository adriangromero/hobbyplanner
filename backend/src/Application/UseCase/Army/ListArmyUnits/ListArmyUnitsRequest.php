<?php

declare(strict_types=1);

namespace App\Application\UseCase\Army\ListArmyUnits;

use App\Domain\ValueObject\ArmyId;

final class ListArmyUnitsRequest
{
    public function __construct(private readonly string $armyId) {}
    public function armyId(): ArmyId { return ArmyId::fromString($this->armyId); }
}
