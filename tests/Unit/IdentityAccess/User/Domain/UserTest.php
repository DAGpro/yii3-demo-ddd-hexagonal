<?php

declare(strict_types=1);

namespace App\Tests\Unit\IdentityAccess\User\Domain;

use App\IdentityAccess\User\Domain\User;
use App\Tests\UnitTester;
use Codeception\Test\Unit;
use DateTimeImmutable;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(User::class)]
final class UserTest extends Unit
{
    private const string TEST_LOGIN = 'test@example.com';

    private const string TEST_PASSWORD = 'password123';

    protected UnitTester $tester;

    public function testCreateUser(): void
    {
        $user = new User(self::TEST_LOGIN, self::TEST_PASSWORD);

        $this->assertSame(self::TEST_LOGIN, $user->getLogin());
        $this->assertNull($user->getId());
        $this->assertNotEmpty($user->getCreatedAt());
        $this->assertNotEmpty($user->getUpdatedAt());
        $this->assertTrue($user->validatePassword(self::TEST_PASSWORD));
    }

    public function testValidatePasswordWithCorrectPassword(): void
    {
        $user = new User(self::TEST_LOGIN, self::TEST_PASSWORD);

        $this->assertTrue($user->validatePassword(self::TEST_PASSWORD));
    }

    public function testValidatePasswordWithIncorrectPassword(): void
    {
        $user = new User(self::TEST_LOGIN, self::TEST_PASSWORD);

        $this->assertFalse($user->validatePassword('wrong_password'));
    }

    public function testSetPassword(): void
    {
        $user = new User(self::TEST_LOGIN, self::TEST_PASSWORD);
        $newPassword = 'new_secure_password';

        $user->setPassword($newPassword);

        $this->assertTrue($user->validatePassword($newPassword));
        $this->assertFalse($user->validatePassword(self::TEST_PASSWORD));
    }

    public function testGetCreatedAt(): void
    {
        $beforeCreation = new DateTimeImmutable();
        $user = new User(self::TEST_LOGIN, self::TEST_PASSWORD);
        $afterCreation = new DateTimeImmutable();

        $createdAt = $user->getCreatedAt();

        $this->assertGreaterThanOrEqual($beforeCreation, $createdAt);
        $this->assertLessThanOrEqual($afterCreation, $createdAt);
    }

    public function testGetUpdatedAt(): void
    {
        $user = new User(self::TEST_LOGIN, self::TEST_PASSWORD);

        $this->assertInstanceOf(DateTimeImmutable::class, $user->getUpdatedAt());
    }
}
