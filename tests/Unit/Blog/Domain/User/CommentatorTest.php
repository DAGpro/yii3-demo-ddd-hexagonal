<?php

declare(strict_types=1);

namespace App\Tests\Unit\Blog\Domain\User;

use App\Blog\Domain\User\Commentator;
use App\Tests\UnitTester;
use Codeception\Test\Unit;
use Override;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(Commentator::class)]
final class CommentatorTest extends Unit
{
    private const int TEST_ID = 1;
    private const string TEST_NAME = 'Test Commentator';

    protected UnitTester $tester;

    private Commentator $commentator;

    public function testCanBeCreated(): void
    {
        $this->assertInstanceOf(Commentator::class, $this->commentator);
    }

    public function testGetIdReturnsCorrectId(): void
    {
        $this->assertSame(self::TEST_ID, $this->commentator->getId());
    }

    public function testGetNameReturnsCorrectName(): void
    {
        $this->assertSame(self::TEST_NAME, $this->commentator->getName());
    }

    public function testIsEqualReturnsTrueForSameCommentator(): void
    {
        $sameCommentator = new Commentator(self::TEST_ID, self::TEST_NAME);
        $this->assertTrue($this->commentator->isEqual($sameCommentator));
    }

    public function testIsEqualReturnsFalseForDifferentId(): void
    {
        $differentCommentator = new Commentator(2, self::TEST_NAME);
        $this->assertFalse($this->commentator->isEqual($differentCommentator));
    }

    public function testIsEqualReturnsFalseForDifferentName(): void
    {
        $differentCommentator = new Commentator(self::TEST_ID, 'Different Name');
        $this->assertFalse($this->commentator->isEqual($differentCommentator));
    }

    public function testIsEqualReturnsFalseForCompletelyDifferentCommentator(): void
    {
        $differentCommentator = new Commentator(2, 'Different Name');
        $this->assertFalse($this->commentator->isEqual($differentCommentator));
    }

    #[Override]
    protected function _before(): void
    {
        $this->commentator = new Commentator(self::TEST_ID, self::TEST_NAME);
    }
}
