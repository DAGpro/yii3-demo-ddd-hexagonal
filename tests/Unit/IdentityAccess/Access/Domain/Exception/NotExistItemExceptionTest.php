<?php

declare(strict_types=1);

namespace App\Tests\Unit\IdentityAccess\Access\Domain\Exception;

use App\IdentityAccess\Access\Domain\Exception\NotExistItemException;
use App\Tests\UnitTester;
use Codeception\Test\Unit;
use PHPUnit\Framework\Attributes\CoversClass;
use RuntimeException;

#[CoversClass(NotExistItemException::class)]
final class NotExistItemExceptionTest extends Unit
{
    protected UnitTester $tester;

    public function testCreateException(): void
    {
        $message = 'Test exception message';
        $code = 100;
        $previous = new RuntimeException('Previous exception');

        $exception = new NotExistItemException($message, $code, $previous);

        $this->assertSame($message, $exception->getMessage());
        $this->assertSame($code, $exception->getCode());
        $this->assertSame($previous, $exception->getPrevious());
    }

    public function testCreateExceptionWithDefaultParameters(): void
    {
        $exception = new NotExistItemException();

        $this->assertSame('', $exception->getMessage());
        $this->assertSame(0, $exception->getCode());
        $this->assertNull($exception->getPrevious());
    }
}
