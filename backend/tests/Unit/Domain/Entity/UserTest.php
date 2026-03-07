<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\Entity;

use App\Domain\Entity\User;
use App\Domain\Exception\ValidationException;
use App\Domain\ValueObject\Email;
use PHPUnit\Framework\TestCase;

final class UserTest extends TestCase
{
    public function testCreate(): void
    {
        $user = User::create(
            new Email('john@example.com'),
            'hashed-password',
            'John Doe',
        );

        $this->assertSame('john@example.com', $user->email()->value());
        $this->assertSame('John Doe', $user->name());
        $this->assertTrue($user->isActive());
        $this->assertContains('ROLE_USER', $user->getRoles());
    }

    public function testUpdateName(): void
    {
        $user = $this->createUser();
        $user->updateName('New Name');

        $this->assertSame('New Name', $user->name());
    }

    public function testUpdateNameEmptyThrows(): void
    {
        $user = $this->createUser();

        $this->expectException(ValidationException::class);
        $user->updateName('   ');
    }

    public function testUpdatePasswordEmptyThrows(): void
    {
        $user = $this->createUser();

        $this->expectException(ValidationException::class);
        $user->updatePassword('   ');
    }

    public function testDeactivateAndActivate(): void
    {
        $user = $this->createUser();

        $user->deactivate();
        $this->assertFalse($user->isActive());

        $user->activate();
        $this->assertTrue($user->isActive());
    }

    public function testGetUserIdentifierReturnsEmail(): void
    {
        $user = $this->createUser();

        $this->assertSame('john@example.com', $user->getUserIdentifier());
    }

    private function createUser(): User
    {
        return User::create(
            new Email('john@example.com'),
            'hashed-password',
            'John Doe',
        );
    }
}
