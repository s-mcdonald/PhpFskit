<?php

declare(strict_types=1);

namespace Tests\FsKit;

use FsKit\Artifacts\DirectoryAttributes;
use FsKit\Directory;
use FsKit\DirectoryListing;
use FsKit\Exceptions\DirectoryNotFoundException;
use FsKit\Exceptions\FileSystemIOException;
use FsKit\File;
use PHPUnit\Framework\TestCase;

class DirectoryTest extends TestCase
{
    private const FIXTURES = __DIR__ . '/../Fixtures/fs';

    private string $workDir;

    protected function setUp(): void
    {
        $this->workDir = sys_get_temp_dir() . '/fskit-dir-test-' . uniqid('', true);
        mkdir($this->workDir, 0755, true);
    }

    protected function tearDown(): void
    {
        if (is_dir($this->workDir)) {
            exec('rm -rf ' . escapeshellarg($this->workDir));
        }
    }

    public function testPathAndName(): void
    {
        $directory = Directory::createByFullPathString('/var/log/nginx');

        self::assertSame('/var/log/nginx', $directory->path());
        self::assertSame('nginx', $directory->name());
    }

    public function testFactoryStripsTrailingSlash(): void
    {
        $directory = Directory::createByFullPathString('/var/log/nginx/');

        self::assertSame('/var/log/nginx', $directory->path());
    }

    public function testExists(): void
    {
        self::assertTrue(Directory::createByFullPathString(self::FIXTURES)->exists());
        self::assertFalse(Directory::createByFullPathString(self::FIXTURES . '/nope')->exists());
    }

    public function testExistsIsFalseForAFilePath(): void
    {
        self::assertFalse(Directory::createByFullPathString(self::FIXTURES . '/file_001.txt')->exists());
    }

    public function testCreateAndDelete(): void
    {
        $directory = Directory::createByFullPathString($this->workDir . '/newly/nested/dir');

        self::assertFalse($directory->exists());
        self::assertTrue($directory->create());
        self::assertTrue($directory->exists());

        self::assertTrue($directory->delete());
        self::assertFalse($directory->exists());
    }

    public function testCreateIsIdempotentWhenDirectoryExists(): void
    {
        $directory = Directory::createByFullPathString($this->workDir);

        self::assertTrue($directory->create());
    }

    public function testDeleteThrowsWhenDirectoryDoesNotExist(): void
    {
        $directory = Directory::createByFullPathString($this->workDir . '/missing');

        $this->expectException(DirectoryNotFoundException::class);

        $directory->delete();
    }

    public function testDeleteThrowsWhenDirectoryIsNotEmpty(): void
    {
        $directory = Directory::createByFullPathString($this->workDir);
        $directory->file('blocker.txt')->touch();

        $this->expectException(FileSystemIOException::class);

        $directory->delete();
    }

    public function testFileBuildsChildHandleWithoutRequiringExistence(): void
    {
        $directory = Directory::createByFullPathString(self::FIXTURES);
        $file = $directory->file('ghost.txt');

        self::assertInstanceOf(File::class, $file);
        self::assertSame(self::FIXTURES . DIRECTORY_SEPARATOR . 'ghost.txt', $file->path());
        self::assertFalse($file->exists());
    }

    public function testSubdirectoryBuildsChildHandle(): void
    {
        $directory = Directory::createByFullPathString(self::FIXTURES);
        $sub = $directory->subdirectory('logs');

        self::assertSame('logs', $sub->name());
        self::assertTrue($sub->exists());
    }

    public function testParentDirectory(): void
    {
        $directory = Directory::createByFullPathString('/var/log/nginx');

        self::assertSame('/var/log', $directory->parentDirectory()->path());
    }

    public function testAttributesReturnsSnapshot(): void
    {
        $attributes = Directory::createByFullPathString(self::FIXTURES)->attributes();

        self::assertInstanceOf(DirectoryAttributes::class, $attributes);
        self::assertGreaterThan(0, $attributes->itemCount());
    }

    public function testAttributesThrowsWhenDirectoryDoesNotExist(): void
    {
        $directory = Directory::createByFullPathString($this->workDir . '/missing');

        $this->expectException(DirectoryNotFoundException::class);

        $directory->attributes();
    }

    public function testLsReturnsListing(): void
    {
        $directory = Directory::createByFullPathString($this->workDir);
        $directory->file('a.txt')->touch();
        $directory->subdirectory('sub')->create();

        $listing = $directory->ls();

        self::assertInstanceOf(DirectoryListing::class, $listing);
        self::assertCount(2, $listing);
        self::assertCount(1, $listing->files());
        self::assertCount(1, $listing->directories());
    }

    public function testLsThrowsWhenDirectoryDoesNotExist(): void
    {
        $directory = Directory::createByFullPathString($this->workDir . '/missing');

        $this->expectException(DirectoryNotFoundException::class);

        $directory->ls();
    }
}
