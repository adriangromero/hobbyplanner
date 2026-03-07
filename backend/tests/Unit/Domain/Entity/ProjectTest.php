<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\Entity;

use App\Domain\Entity\Project;
use App\Domain\Exception\ValidationException;
use App\Domain\ValueObject\ProjectId;
use App\Domain\ValueObject\UserId;
use PHPUnit\Framework\TestCase;

final class ProjectTest extends TestCase
{
    public function testConstruct(): void
    {
        $project = new Project(
            ProjectId::create(),
            UserId::create(),
            'My Project',
            'A description',
        );

        $this->assertSame('My Project', $project->name());
        $this->assertSame('A description', $project->description());
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

    public function testTimestampsAreSet(): void
    {
        $project = $this->createProject();

        $this->assertNotNull($project->createdAt());
        $this->assertNotNull($project->updatedAt());
    }

    private function createProject(): Project
    {
        return new Project(
            ProjectId::create(),
            UserId::create(),
            'Test Project',
            'Test Description',
        );
    }
}
