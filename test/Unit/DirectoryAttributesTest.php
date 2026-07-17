<?php

declare(strict_types=1);

namespace Tests\FsKit;

use FsKit\Artifacts\DirectoryAttributes;
use FsKit\Artifacts\FDType;
use FsKit\Artifacts\FsoType;
use FsKit\Exceptions\DirectoryNotFoundException;
use PHPUnit\Framework\TestCase;

class DirectoryAttributesTest extends TestCase
{
    private string $workDir;

    protected function setUp(): void
    {
        $this->workDir = sys_get_temp_dir() . '/fskit-dattr-test-' . uniqid('', true);
        mkdir($this->workDir, 0755, true);
    }

    protected function tearDown(): void
    {
        if (is_dir($this->workDir)) {
            exec('rm -rf ' . escapeshellarg($this->workDir));
        }
    }

    public function testConstructorThrowsWhenDirectoryDoesNotExist(): void
    {
        $this->expectException(DirectoryNotFoundException::class);

        new DirectoryAttributes($this->workDir . '/missing');
    }

    public function testSnapshotFields(): void
    {
        chmod($this->workDir, 0755);
        touch($this->workDir . '/a.txt');
        touch($this->workDir . '/b.txt');
        mkdir($this->workDir . '/sub');

        $attributes = new DirectoryAttributes($this->workDir);

        self::assertSame($this->workDir, $attributes->path());
        self::assertSame(FsoType::Directory, $attributes->type());
        self::assertSame(FDType::Directory, $attributes->fdType());
        self::assertSame('0755', $attributes->visibility());
        self::assertSame(3, $attributes->itemCount());
        self::assertIsInt($attributes->dateCreated());
        self::assertIsInt($attributes->lastModified());
    }

    public function testItemCountExcludesDotEntriesInEmptyDirectory(): void
    {
        $attributes = new DirectoryAttributes($this->workDir);

        self::assertSame(0, $attributes->itemCount());
    }

    public function testSnapshotIsPointInTimeNotLive(): void
    {
        $attributes = new DirectoryAttributes($this->workDir);

        touch($this->workDir . '/added_later.txt');

        self::assertSame(0, $attributes->itemCount());
    }
}
