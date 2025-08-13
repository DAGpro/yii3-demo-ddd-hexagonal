<?php

declare(strict_types=1);

namespace App\Tests\Unit\Blog\Application\Service\AppService\QueryService;

use App\Blog\Application\Service\AppService\QueryService\ArchivePostQueryService;
use App\Blog\Domain\Port\PostRepositoryInterface;
use Override;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Yiisoft\Data\Reader\DataReaderInterface;

#[CoversClass(ArchivePostQueryService::class)]
final class ArchivePostQueryServiceTest extends TestCase
{
    private ArchivePostQueryService $service;
    private PostRepositoryInterface&MockObject $postRepository;
    private DataReaderInterface&MockObject $dataReader;

    public function testGetFullArchiveWithoutLimit(): void
    {
        $this->postRepository
            ->expects($this->once())
            ->method('getFullArchive')
            ->with(null)
            ->willReturn($this->dataReader);

        $result = $this->service->getFullArchive();

        $this->assertSame($this->dataReader, $result);
    }

    public function testGetFullArchiveWithLimit(): void
    {
        $limit = 5;

        $this->postRepository
            ->expects($this->once())
            ->method('getFullArchive')
            ->with($limit)
            ->willReturn($this->dataReader);

        $result = $this->service->getFullArchive($limit);

        $this->assertSame($this->dataReader, $result);
    }

    public function testGetMonthlyArchive(): void
    {
        $year = 2023;
        $month = 5;

        $this->postRepository
            ->expects($this->once())
            ->method('getMonthlyArchive')
            ->with($year, $month)
            ->willReturn($this->dataReader);

        $result = $this->service->getMonthlyArchive($year, $month);

        $this->assertSame($this->dataReader, $result);
    }

    public function testGetYearlyArchive(): void
    {
        $year = 2023;

        $this->postRepository
            ->expects($this->once())
            ->method('getYearlyArchive')
            ->with($year)
            ->willReturn($this->dataReader);

        $result = $this->service->getYearlyArchive($year);

        $this->assertSame($this->dataReader, $result);
    }

    /**
     * @throws Exception
     */
    #[Override]
    protected function setUp(): void
    {
        $this->postRepository = $this->createMock(PostRepositoryInterface::class);
        $this->dataReader = $this->createMock(DataReaderInterface::class);
        $this->service = new ArchivePostQueryService($this->postRepository);
    }
}
