<?php

declare(strict_types=1);

namespace App\Tests\Unit\IdentityAccess\Access\Domain\Exception;

use App\IdentityAccess\Access\Domain\Exception\ExistItemException;
use App\Tests\UnitTester;
use Codeception\Test\Unit;
use PHPUnit\Framework\Attributes\CoversClass;
use RuntimeException;

#[CoversClass(ExistItemException::class)]
final class ExistItemExceptionTest extends Unit
{
    protected UnitTester $tester;

    public function testCreateException(): void
    {
        $message = 'Test exception message';
        $code = 100;
        $previous = new RuntimeException('Previous exception');

        $exception = new ExistItemException($message, $code, $previous);

        $this->assertSame($message, $exception->getMessage());
        $this->assertSame($code, $exception->getCode());
        $this->assertSame($previous, $exception->getPrevious());
    }

    public function testCreateExceptionWithDefaultParameters(): void
    {
        $exception = new ExistItemException();

        $this->assertSame('', $exception->getMessage());
        $this->assertSame(0, $exception->getCode());
        $this->assertNull($exception->getPrevious());
    }
}
