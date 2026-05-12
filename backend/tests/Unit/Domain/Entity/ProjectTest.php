<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\Entity;

use App\Domain\Entity\Project;
use App\Domain\Exception\ValidationException;
use App\Domain\ValueObject\ProjectStatus;
use App\Domain\ValueObject\UserId;
use PHPUnit\Framework\TestCase;

final class ProjectTest extends TestCase
{
    public function testCreate(): void
    {
        $project = Project::create(
            UserId::create(),
            'My Project',
            'A description',
        );

        $this->assertSame('My Project', $project->name());
        $this->assertSame('A description', $project->description());
        $this->assertSame(ProjectStatus::ACTIVE, $project->status());
    }

    public function testCreateEmptyNameThrows(): void
    {
        $this->expectException(ValidationException::class);

        Project::create(UserId::create(), '   ', 'desc');
    }

    public function testRename(): void
    {
        $project = $this->createProject();
        $project->rename('New Name');

        $this->assertSame('New Name', $project->name());
    }

    public function testRenameEmptyThrows(): void
    {
        $project = $this->createProject();

        $this->expectException(ValidationException::class);
        $project->rename('   ');
    }

    public function testUpdateDescriptionAllowsEmpty(): void
    {
        $project = $this->createProject();
        $project->updateDescription('');

        $this->assertSame('', $project->description());
    }

    public function testMarkAsCompleted(): void
    {
        $project = $this->createProject();
        $project->markAsCompleted();

        $this->assertSame(ProjectStatus::COMPLETED, $project->status());
    }

    public function testMarkAsActiveAfterCompleted(): void
    {
        $project = $this->createProject();
        $project->markAsCompleted();
        $project->markAsActive();

        $this->assertSame(ProjectStatus::ACTIVE, $project->status());
    }

    public function testTimestampsAreSet(): void
    {
        $project = $this->createProject();

        $this->assertNotNull($project->createdAt());
        $this->assertNotNull($project->updatedAt());
    }

    private function createProject(): Project
    {
        return Project::create(
            UserId::create(),
            'Test Project',
            'Test Description',
        );
    }
}
