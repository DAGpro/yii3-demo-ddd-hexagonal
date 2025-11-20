<?php

declare(strict_types=1);

namespace App\Tests\Unit\IdentityAccess\User\Infrastructure\Authentication;

use App\IdentityAccess\User\Infrastructure\Authentication\AuthenticationException;
use App\Tests\UnitTester;
use Codeception\Test\Unit;
use PHPUnit\Framework\Attributes\CoversClass;
use RuntimeException;

#[CoversClass(AuthenticationException::class)]
final class AuthenticationExceptionTest extends Unit
{
    protected UnitTester $tester;

    public function testCreateException(): void
    {
        $message = 'Authentication failed';
        $code = 403;
        $previous = new RuntimeException('Previous exception');

        $exception = new AuthenticationException($message, $code, $previous);

        $this->assertSame($message, $exception->getMessage());
        $this->assertSame($code, $exception->getCode());
        $this->assertSame($previous, $exception->getPrevious());
    }

    public function testCreateExceptionWithDefaultParameters(): void
    {
        $exception = new AuthenticationException();

        $this->assertSame('', $exception->getMessage());
        $this->assertSame(0, $exception->getCode());
        $this->assertNull($exception->getPrevious());
    }
}
