<?php

declare(strict_types=1);

namespace App\Tests\Unit\IdentityAccess\Access\Application\Service;

use App\IdentityAccess\Access\Slice\Service\PermissionDTO;
use App\Tests\UnitTester;
use Codeception\Test\Unit;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(PermissionDTO::class)]
final class PermissionDTOTest extends Unit
{
    protected UnitTester $tester;

    public function testCreatePermissionDTOWithAllFields(): void
    {
        $name = 'edit_article';
        $description = 'Allows editing articles';
        $createdAt = 1620000000;
        $updatedAt = 1620003600;

        $dto = new PermissionDTO($name, $description, $createdAt, $updatedAt);

        $this->assertSame($name, $dto->getName());
        $this->assertSame($description, $dto->getDescription());
        $this->assertSame($createdAt, $dto->getCreatedAt());
        $this->assertSame($updatedAt, $dto->getUpdatedAt());
    }

    public function testCreatePermissionDTOWithRequiredFieldsOnly(): void
    {
        $name = 'view_article';
        $dto = new PermissionDTO($name);

        $this->assertSame($name, $dto->getName());
        $this->assertSame('', $dto->getDescription());
        $this->assertNull($dto->getCreatedAt());
        $this->assertNull($dto->getUpdatedAt());
    }

    public function testGetDescriptionReturnsEmptyStringWhenNull(): void
    {
        $dto = new PermissionDTO('delete_article');
        $this->assertSame('', $dto->getDescription());
    }

    public function testGetDescriptionReturnsValueWhenNotNull(): void
    {
        $description = 'Allows deleting articles';
        $dto = new PermissionDTO('delete_article', $description);
        $this->assertSame($description, $dto->getDescription());
    }
}
