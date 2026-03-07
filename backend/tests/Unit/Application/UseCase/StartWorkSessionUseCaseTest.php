<?php

declare(strict_types=1);

namespace App\Tests\Unit\Application\UseCase;

use App\Application\UseCase\WorkSession\StartWorkSession\StartWorkSessionRequest;
use App\Application\UseCase\WorkSession\StartWorkSession\StartWorkSessionUseCase;
use App\Domain\Entity\Item;
use App\Domain\Entity\Project;
use App\Domain\Entity\WorkSession;
use App\Domain\Exception\ActiveSessionExistsException;
use App\Domain\Exception\ItemNotFoundException;
use App\Domain\Exception\ProjectNotFoundException;
use App\Domain\Repository\ItemRepositoryInterface;
use App\Domain\Repository\ProjectRepositoryInterface;
use App\Domain\Repository\WorkSessionRepositoryInterface;
use App\Domain\ValueObject\ItemId;
use App\Domain\ValueObject\ProjectId;
use App\Domain\ValueObject\UserId;
use App\Domain\ValueObject\WorkSessionId;
use DateTimeImmutable;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

final class StartWorkSessionUseCaseTest extends TestCase
{
    private WorkSessionRepositoryInterface&MockObject $sessionRepository;
    private ItemRepositoryInterface&MockObject $itemRepository;
    private ProjectRepositoryInterface&MockObject $projectRepository;
    private StartWorkSessionUseCase $useCase;

    private ItemId $itemId;
    private ProjectId $projectId;
    private UserId $userId;

    protected function setUp(): void
    {
        $this->sessionRepository = $this->createMock(WorkSessionRepositoryInterface::class);
        $this->itemRepository    = $this->createMock(ItemRepositoryInterface::class);
        $this->projectRepository = $this->createMock(ProjectRepositoryInterface::class);
        $this->useCase           = new StartWorkSessionUseCase(
            $this->sessionRepository,
            $this->itemRepository,
            $this->projectRepository,
        );

        $this->itemId    = ItemId::create();
        $this->projectId = ProjectId::create();
        $this->userId    = UserId::create();
    }

    public function testSuccessfulStart(): void
    {
        $this->sessionRepository->method('findActiveByUser')->willReturn(null);

        $item = new Item($this->itemId, $this->projectId, $this->userId, 'Item', 5.0);
        $this->itemRepository->method('findById')->willReturn($item);

        $project = new Project($this->projectId, $this->userId, 'Project', 'Desc');
        $this->projectRepository->method('findById')->willReturn($project);

        $this->sessionRepository->expects($this->once())->method('save');

        $response = $this->useCase->execute(new StartWorkSessionRequest(
            $this->itemId->value(),
            $this->projectId->value(),
            $this->userId->value(),
        ));

        $this->assertNotNull($response->session());
    }

    public function testActiveSessionExistsThrows(): void
    {
        $activeSession = new WorkSession(
            WorkSessionId::create(),
            $this->projectId,
            $this->itemId,
            $this->userId,
            new DateTimeImmutable(),
            null,
        );

        $this->sessionRepository->method('findActiveByUser')->willReturn($activeSession);

        $this->expectException(ActiveSessionExistsException::class);

        $this->useCase->execute(new StartWorkSessionRequest(
            $this->itemId->value(),
            $this->projectId->value(),
            $this->userId->value(),
        ));
    }

    public function testItemNotFoundThrows(): void
    {
        $this->sessionRepository->method('findActiveByUser')->willReturn(null);
        $this->itemRepository->method('findById')->willReturn(null);

        $this->expectException(ItemNotFoundException::class);

        $this->useCase->execute(new StartWorkSessionRequest(
            $this->itemId->value(),
            $this->projectId->value(),
            $this->userId->value(),
        ));
    }

    public function testProjectNotFoundThrows(): void
    {
        $this->sessionRepository->method('findActiveByUser')->willReturn(null);

        $item = new Item($this->itemId, $this->projectId, $this->userId, 'Item', 5.0);
        $this->itemRepository->method('findById')->willReturn($item);
        $this->projectRepository->method('findById')->willReturn(null);

        $this->expectException(ProjectNotFoundException::class);

        $this->useCase->execute(new StartWorkSessionRequest(
            $this->itemId->value(),
            $this->projectId->value(),
            $this->userId->value(),
        ));
    }
}
