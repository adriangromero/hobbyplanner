<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\ValueObject;

use App\Domain\Exception\InvalidEmailException;
use App\Domain\ValueObject\Email;
use PHPUnit\Framework\TestCase;

final class EmailTest extends TestCase
{
    public function testValidEmail(): void
    {
        $email = Email::fromString('Test@Example.COM');

        $this->assertSame('test@example.com', $email->value());
    }

    public function testInvalidEmailThrowsException(): void
    {
        $this->expectException(InvalidEmailException::class);

        Email::fromString('not-an-email');
    }

    public function testEmptyEmailThrowsException(): void
    {
        $this->expectException(InvalidEmailException::class);

        Email::fromString('');
    }

    public function testFromString(): void
    {
        $email = Email::fromString('user@test.com');

        $this->assertSame('user@test.com', $email->value());
    }

    public function testEquals(): void
    {
        $a = Email::fromString('user@test.com');
        $b = Email::fromString('USER@TEST.COM');

        $this->assertTrue($a->equals($b));
    }

    public function testNotEquals(): void
    {
        $a = Email::fromString('user1@test.com');
        $b = Email::fromString('user2@test.com');

        $this->assertFalse($a->equals($b));
    }

    public function testToString(): void
    {
        $email = Email::fromString('User@Test.com');

        $this->assertSame('user@test.com', (string) $email);
    }
}
