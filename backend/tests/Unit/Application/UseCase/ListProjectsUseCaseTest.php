<?php

declare(strict_types=1);

namespace App\Tests\Unit\Application\UseCase;

use App\Application\UseCase\Project\ListProjects\ListProjectsRequest;
use App\Application\UseCase\Project\ListProjects\ListProjectsUseCase;
use App\Domain\Entity\Project;
use App\Domain\Repository\ProjectRepositoryInterface;
use App\Domain\Repository\UnitRepositoryInterface;
use App\Domain\ValueObject\ProjectType;
use App\Domain\ValueObject\UserId;
use PHPUnit\Framework\TestCase;

final class ListProjectsUseCaseTest extends TestCase
{
    public function testArmyProjectListIncludesUnitAndMiniatureCounts(): void
    {
        $userId = UserId::create();
        $army = Project::create($userId, 'Talabheim', '', ProjectType::ARMY);
        $general = Project::create($userId, 'Terrain', 'Scenery');

        $projects = $this->createMock(ProjectRepositoryInterface::class);
        $projects->method('findByUser')->with($userId)->willReturn([$army, $general]);

        $units = $this->createMock(UnitRepositoryInterface::class);
        $units->expects(self::once())->method('inventorySummaryByProjects')->with(self::callback(
            static fn(array $ids): bool => count($ids) === 1 && $ids[0]->equals($army->id()),
        ))->willReturn([
            $army->id()->value() => ['unitCount' => 4, 'miniatureCount' => 27],
        ]);

        $response = (new ListProjectsUseCase($projects, $units))->execute(
            new ListProjectsRequest($userId->value()),
        );
        $listed = array_map(static fn($dto): array => $dto->toArray(), $response->projects());

        self::assertSame(4, $listed[0]['unitCount']);
        self::assertSame(27, $listed[0]['miniatureCount']);
        self::assertSame(0, $listed[1]['unitCount']);
        self::assertSame(0, $listed[1]['miniatureCount']);
    }
}
