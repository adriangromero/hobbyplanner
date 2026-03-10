<?php

declare(strict_types=1);

namespace App\Tests\Unit\Application\UseCase;

use App\Application\Port\CurrentUserProvider;
use App\Application\Security\OwnershipGuard;
use App\Application\UseCase\Project\DeleteProject\DeleteProjectRequest;
use App\Application\UseCase\Project\DeleteProject\DeleteProjectUseCase;
use App\Domain\Entity\Project;
use App\Domain\Exception\AccessDeniedException;
use App\Domain\Exception\ProjectNotFoundException;
use App\Domain\Repository\ItemRepositoryInterface;
use App\Domain\Repository\ProjectRepositoryInterface;
use App\Domain\Repository\WorkSessionRepositoryInterface;
use App\Domain\ValueObject\ProjectId;
use App\Domain\ValueObject\UserId;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

final class DeleteProjectUseCaseTest extends TestCase
{
    private ProjectRepositoryInterface&MockObject $projectRepository;
    private ItemRepositoryInterface&MockObject $itemRepository;
    private WorkSessionRepositoryInterface&MockObject $sessionRepository;
    private CurrentUserProvider&MockObject $currentUserProvider;
    private DeleteProjectUseCase $useCase;

    protected function setUp(): void
    {
        $this->projectRepository  = $this->createMock(ProjectRepositoryInterface::class);
        $this->itemRepository     = $this->createMock(ItemRepositoryInterface::class);
        $this->sessionRepository  = $this->createMock(WorkSessionRepositoryInterface::class);
        $this->currentUserProvider = $this->createMock(CurrentUserProvider::class);
        $this->useCase            = new DeleteProjectUseCase(
            $this->projectRepository,
            $this->itemRepository,
            $this->sessionRepository,
            new OwnershipGuard($this->currentUserProvider),
        );
    }

    public function testDeletesCascadeInCorrectOrder(): void
    {
        $userId    = UserId::create();
        $projectId = ProjectId::create();
        $project   = new Project($projectId, $userId, 'Project', 'Desc');

        $this->currentUserProvider->method('currentUserId')->willReturn($userId);
        $this->projectRepository->method('findById')->willReturn($project);

        $callOrder = [];

        $this->sessionRepository
            ->expects($this->once())
            ->method('deleteByProject')
            ->willReturnCallback(function () use (&$callOrder) { $callOrder[] = 'deleteSessions'; });

        $this->itemRepository
            ->expects($this->once())
            ->method('deleteByProject')
            ->willReturnCallback(function () use (&$callOrder) { $callOrder[] = 'deleteItems'; });

        $this->projectRepository
            ->expects($this->once())
            ->method('delete')
            ->willReturnCallback(function () use (&$callOrder) { $callOrder[] = 'deleteProject'; });

        $this->useCase->execute(new DeleteProjectRequest($projectId->value()));

        $this->assertSame(['deleteSessions', 'deleteItems', 'deleteProject'], $callOrder);
    }

    public function testProjectNotFoundThrows(): void
    {
        $this->projectRepository->method('findById')->willReturn(null);

        $this->expectException(ProjectNotFoundException::class);

        $this->useCase->execute(new DeleteProjectRequest(ProjectId::create()->value()));
    }

    public function testDeniesAccessWhenUserDoesNotOwnProject(): void
    {
        $ownerId   = UserId::create();
        $otherId   = UserId::create();
        $projectId = ProjectId::create();
        $project   = new Project($projectId, $ownerId, 'Project', 'Desc');

        $this->currentUserProvider->method('currentUserId')->willReturn($otherId);
        $this->projectRepository->method('findById')->willReturn($project);

        $this->expectException(AccessDeniedException::class);

        $this->useCase->execute(new DeleteProjectRequest($projectId->value()));
    }
}
