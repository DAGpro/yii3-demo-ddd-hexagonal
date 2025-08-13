<?php

declare(strict_types=1);

namespace App\Tests\Unit\IdentityAccess\User\Infrastructure\Authentication;

use App\IdentityAccess\User\Domain\User;
use App\IdentityAccess\User\Infrastructure\Authentication\Identity;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Yiisoft\User\Login\Cookie\CookieLoginIdentityInterface;

#[CoversClass(Identity::class)]
final class IdentityTest extends TestCase
{
    private User|MockObject $user;
    private Identity $identity;

    public function testImplementsCookieLoginIdentityInterface(): void
    {
        $this->assertInstanceOf(CookieLoginIdentityInterface::class, $this->identity);
    }

    public function testGetId(): void
    {
        $this->assertSame('', $this->identity->getId());
    }

    public function testGetUser(): void
    {
        $this->assertSame($this->user, $this->identity->getUser());
    }

    public function testGetCookieLoginKey(): void
    {
        $key = $this->identity->getCookieLoginKey();
        $this->assertNotEmpty($key);
        $this->assertSame(32, strlen($key));
    }

    public function testValidateCookieLoginKey(): void
    {
        $key = $this->identity->getCookieLoginKey();
        $this->assertTrue($this->identity->validateCookieLoginKey($key));
        $this->assertFalse($this->identity->validateCookieLoginKey('invalid_key'));
    }

    public function testRegenerateCookieLoginKey(): void
    {
        $oldKey = $this->identity->getCookieLoginKey();
        $this->identity->regenerateCookieLoginKey();
        $newKey = $this->identity->getCookieLoginKey();

        $this->assertNotSame($oldKey, $newKey);
        $this->assertSame(32, strlen($newKey));
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = $this->createMock(User::class);
        $this->identity = new Identity($this->user);
    }
}
