<?php

declare(strict_types=1);

namespace App\Tests\Unit\Blog\Domain\User;

use App\Blog\Domain\User\Author;
use App\Tests\UnitTester;
use Codeception\Test\Unit;
use Override;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(Author::class)]
final class AuthorTest extends Unit
{
    private const int TEST_ID = 1;

    private const string TEST_NAME = 'Test Author';

    protected UnitTester $tester;

    private Author $author;

    public function testCanBeCreated(): void
    {
        $this->assertInstanceOf(Author::class, $this->author);
    }

    public function testGetIdReturnsCorrectId(): void
    {
        $this->assertSame(self::TEST_ID, $this->author->getId());
    }

    public function testGetNameReturnsCorrectName(): void
    {
        $this->assertSame(self::TEST_NAME, $this->author->getName());
    }

    public function testIsEqualReturnsTrueForSameAuthor(): void
    {
        $sameAuthor = new Author(self::TEST_ID, self::TEST_NAME);
        $this->assertTrue($this->author->isEqual($sameAuthor));
    }

    public function testIsEqualReturnsFalseForDifferentId(): void
    {
        $differentAuthor = new Author(2, self::TEST_NAME);
        $this->assertFalse($this->author->isEqual($differentAuthor));
    }

    public function testIsEqualReturnsFalseForDifferentName(): void
    {
        $differentAuthor = new Author(self::TEST_ID, 'Different Name');
        $this->assertFalse($this->author->isEqual($differentAuthor));
    }

    public function testIsEqualReturnsFalseForCompletelyDifferentAuthor(): void
    {
        $differentAuthor = new Author(2, 'Different Name');
        $this->assertFalse($this->author->isEqual($differentAuthor));
    }

    #[Override]
    protected function _before(): void
    {
        $this->author = new Author(self::TEST_ID, self::TEST_NAME);
    }
}
