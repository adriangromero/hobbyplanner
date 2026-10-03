<?php

declare(strict_types=1);
namespace App\Application\UseCase\Army\ListArmies;
use App\Application\DTO\ArmyDTO;
use App\Domain\Repository\ArmyRepositoryInterface;
final class ListArmiesUseCase
{
    public function __construct(private readonly ArmyRepositoryInterface $armies) {}
    public function execute(ListArmiesRequest $request): array
    {
        return array_map(ArmyDTO::fromEntity(...), $this->armies->findByUser($request->userId()));
    }
}
