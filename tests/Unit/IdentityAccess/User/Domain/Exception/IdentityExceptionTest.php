<?php

declare(strict_types=1);

namespace App\Tests\Unit\IdentityAccess\User\Domain\Exception;

use App\IdentityAccess\User\Domain\Exception\IdentityException;
use App\Tests\UnitTester;
use Codeception\Test\Unit;
use PHPUnit\Framework\Attributes\CoversClass;
use RuntimeException;

#[CoversClass(IdentityException::class)]
final class IdentityExceptionTest extends Unit
{
    protected UnitTester $tester;

    public function testCreateException(): void
    {
        $message = 'Test exception message';
        $code = 100;
        $previous = new RuntimeException('Previous exception');

        $exception = new IdentityException($message, $code, $previous);

        $this->assertSame($message, $exception->getMessage());
        $this->assertSame($code, $exception->getCode());
        $this->assertSame($previous, $exception->getPrevious());
    }

    public function testCreateExceptionWithDefaultParameters(): void
    {
        $exception = new IdentityException();

        $this->assertSame('', $exception->getMessage());
        $this->assertSame(0, $exception->getCode());
        $this->assertNull($exception->getPrevious());
    }
}
