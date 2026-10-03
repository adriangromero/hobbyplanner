<?php

declare(strict_types=1);
namespace App\Application\UseCase\Army\CreateArmy;
use App\Application\DTO\ArmyDTO;
use App\Domain\Entity\Army;
use App\Domain\Repository\ArmyRepositoryInterface;
final class CreateArmyUseCase
{
    public function __construct(private readonly ArmyRepositoryInterface $armies) {}
    public function execute(CreateArmyRequest $request): ArmyDTO
    {
        $army = Army::create($request->userId(), $request->name());
        $this->armies->save($army);
        return ArmyDTO::fromEntity($army);
    }
}
