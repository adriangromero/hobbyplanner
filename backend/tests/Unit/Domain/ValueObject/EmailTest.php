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
        $email = new Email('Test@Example.COM');

        $this->assertSame('test@example.com', $email->value());
    }

    public function testInvalidEmailThrowsException(): void
    {
        $this->expectException(InvalidEmailException::class);

        new Email('not-an-email');
    }

    public function testEmptyEmailThrowsException(): void
    {
        $this->expectException(InvalidEmailException::class);

        new Email('');
    }

    public function testFromString(): void
    {
        $email = Email::fromString('user@test.com');

        $this->assertSame('user@test.com', $email->value());
    }

    public function testEquals(): void
    {
        $a = new Email('user@test.com');
        $b = new Email('USER@TEST.COM');

        $this->assertTrue($a->equals($b));
    }

    public function testNotEquals(): void
    {
        $a = new Email('user1@test.com');
        $b = new Email('user2@test.com');

        $this->assertFalse($a->equals($b));
    }

    public function testToString(): void
    {
        $email = new Email('User@Test.com');

        $this->assertSame('user@test.com', (string) $email);
    }
}
