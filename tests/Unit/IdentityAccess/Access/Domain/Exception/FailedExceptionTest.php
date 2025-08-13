<?php

declare(strict_types=1);

namespace App\Tests\Unit\IdentityAccess\Access\Domain\Exception;

use App\IdentityAccess\Access\Domain\Exception\FailedException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use RuntimeException;

#[CoversClass(FailedException::class)]
final class FailedExceptionTest extends TestCase
{
    public function testCreateException(): void
    {
        $message = 'Test exception message';
        $code = 100;
        $previous = new RuntimeException('Previous exception');

        $exception = new FailedException($message, $code, $previous);

        $this->assertSame($message, $exception->getMessage());
        $this->assertSame($code, $exception->getCode());
        $this->assertSame($previous, $exception->getPrevious());
    }

    public function testCreateExceptionWithDefaultParameters(): void
    {
        $exception = new FailedException();

        $this->assertSame('', $exception->getMessage());
        $this->assertSame(0, $exception->getCode());
        $this->assertNull($exception->getPrevious());
    }
}
