<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\ValueObject;

use App\Domain\Exception\ValidationException;
use App\Domain\ValueObject\UserId;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\Uuid;

final class UserIdTest extends TestCase
{
    public function testCreateGeneratesValidUuid(): void
    {
        $id = UserId::create();

        $this->assertTrue(Uuid::isValid($id->value()));
    }

    public function testFromStringWithValidUuid(): void
    {
        $uuid = Uuid::uuid4()->toString();
        $id   = UserId::fromString($uuid);

        $this->assertSame($uuid, $id->value());
    }

    public function testFromStringWithInvalidUuidThrows(): void
    {
        $this->expectException(ValidationException::class);

        UserId::fromString('not-a-uuid');
    }

    public function testEquals(): void
    {
        $uuid = Uuid::uuid4()->toString();
        $a    = UserId::fromString($uuid);
        $b    = UserId::fromString($uuid);

        $this->assertTrue($a->equals($b));
    }

    public function testNotEquals(): void
    {
        $a = UserId::create();
        $b = UserId::create();

        $this->assertFalse($a->equals($b));
    }

    public function testToString(): void
    {
        $uuid = Uuid::uuid4()->toString();
        $id   = UserId::fromString($uuid);

        $this->assertSame($uuid, (string) $id);
    }
}
