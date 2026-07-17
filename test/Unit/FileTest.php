<?php

declare(strict_types=1);

namespace Tests\FsKit;

use FsKit\Artifacts\FDType;
use FsKit\Artifacts\FileAttributes;
use FsKit\Artifacts\FsoSize;
use FsKit\Artifacts\FsoType;
use FsKit\Exceptions\FileNotFoundException;
use FsKit\File;
use PHPUnit\Framework\TestCase;

class FileTest extends TestCase
{
    private string $workDir;

    protected function setUp(): void
    {
        $this->workDir = sys_get_temp_dir() . '/fskit-file-test-' . uniqid('', true);
        mkdir($this->workDir, 0755, true);
    }

    protected function tearDown(): void
    {
        foreach (glob($this->workDir . '/*') ?: [] as $entry) {
            is_dir($entry) ? rmdir($entry) : unlink($entry);
        }
        rmdir($this->workDir);
    }

    public function testParentDirectory(): void
    {
        $file = File::createByFullFilenamePathString(__DIR__ . '/../Fixtures/fs/logs/2024/02/file_062.txt');

        self::assertEquals('02', $file->parentDirectory()->name());
    }

    public function testPath(): void
    {
        $file = File::createByFullFilenamePathString(__DIR__ . '/../Fixtures/fs/logs/2024/02/file_062.txt');

        self::assertStringContainsString('2024/02/file_062.txt', $file->path());
    }

    public function testFileHandleReportsNameAndExtension(): void
    {
        $file = File::createByFullFilenamePathString($this->workDir . '/example.txt');

        self::assertSame('example.txt', $file->name());
        self::assertSame('txt', $file->extension());
        self::assertFalse($file->exists());

        $file->touch();

        self::assertTrue($file->exists());
    }

    public function testExtensionIsEmptyForFileWithoutOne(): void
    {
        $file = File::createByFullFilenamePathString($this->workDir . '/no_extension');

        self::assertSame('', $file->extension());
    }

    public function testConstructingAHandleDoesNotTouchDisk(): void
    {
        $file = File::createByFullFilenamePathString($this->workDir . '/never_created.txt');

        self::assertFalse($file->exists());
        self::assertFileDoesNotExist($file->path());
    }

    public function testWriteReadAndAppend(): void
    {
        $file = File::createByFullFilenamePathString($this->workDir . '/notes.txt');

        self::assertSame(5, $file->write('hello'));
        self::assertSame('hello', $file->read());

        self::assertSame(6, $file->append(' world'));
        self::assertSame('hello world', $file->read());

        self::assertSame(9, $file->write('overwrite'));
        self::assertSame('overwrite', $file->read());
    }

    public function testReadThrowsWhenFileDoesNotExist(): void
    {
        $file = File::createByFullFilenamePathString($this->workDir . '/missing.txt');

        $this->expectException(FileNotFoundException::class);

        $file->read();
    }

    public function testSizeReturnsFsoSize(): void
    {
        $file = File::createByFullFilenamePathString($this->workDir . '/sized.txt');
        $file->write('12345');

        $size = $file->size();

        self::assertInstanceOf(FsoSize::class, $size);
        self::assertSame(5, $size->getInBytes());
    }

    public function testSizeIsNullWhenFileDoesNotExist(): void
    {
        $file = File::createByFullFilenamePathString($this->workDir . '/missing.txt');

        self::assertNull($file->size());
    }

    public function testIsReadableAndIsWritable(): void
    {
        $file = File::createByFullFilenamePathString($this->workDir . '/perms.txt');

        self::assertFalse($file->isReadable());
        self::assertFalse($file->isWritable());

        $file->write('content');

        self::assertTrue($file->isReadable());
        self::assertTrue($file->isWritable());
    }

    public function testDelete(): void
    {
        $file = File::createByFullFilenamePathString($this->workDir . '/doomed.txt');
        $file->touch();

        self::assertTrue($file->delete());
        self::assertFalse($file->exists());
    }

    public function testDeleteThrowsWhenFileDoesNotExist(): void
    {
        $file = File::createByFullFilenamePathString($this->workDir . '/missing.txt');

        $this->expectException(FileNotFoundException::class);

        $file->delete();
    }

    public function testCopyToReturnsNewHandleAndKeepsOriginal(): void
    {
        $source = File::createByFullFilenamePathString($this->workDir . '/source.txt');
        $source->write('payload');

        $copy = $source->copyTo($this->workDir . '/copy.txt');

        self::assertTrue($source->exists());
        self::assertTrue($copy->exists());
        self::assertSame('payload', $copy->read());
        self::assertNotSame($source, $copy);
    }

    public function testMoveToReturnsNewHandleAndRemovesOriginal(): void
    {
        $source = File::createByFullFilenamePathString($this->workDir . '/source.txt');
        $source->write('payload');

        $moved = $source->moveTo($this->workDir . '/moved.txt');

        self::assertFalse($source->exists());
        self::assertTrue($moved->exists());
        self::assertSame('payload', $moved->read());
    }

    public function testCopyToThrowsWhenSourceDoesNotExist(): void
    {
        $file = File::createByFullFilenamePathString($this->workDir . '/missing.txt');

        $this->expectException(FileNotFoundException::class);

        $file->copyTo($this->workDir . '/copy.txt');
    }

    public function testMoveToThrowsWhenSourceDoesNotExist(): void
    {
        $file = File::createByFullFilenamePathString($this->workDir . '/missing.txt');

        $this->expectException(FileNotFoundException::class);

        $file->moveTo($this->workDir . '/moved.txt');
    }

    public function testFsoTypeAndFdType(): void
    {
        $file = File::createByFullFilenamePathString($this->workDir . '/typed.txt');
        $file->touch();

        self::assertSame(FsoType::Filename, $file->fsoType());
        self::assertSame(FDType::File, $file->fdType());
    }

    public function testFsoTypeThrowsWhenFileDoesNotExist(): void
    {
        $file = File::createByFullFilenamePathString($this->workDir . '/missing.txt');

        $this->expectException(FileNotFoundException::class);

        $file->fsoType();
    }

    public function testAttributesReturnsSnapshot(): void
    {
        $file = File::createByFullFilenamePathString($this->workDir . '/attr.txt');
        $file->write('abc');

        $attributes = $file->attributes();

        self::assertInstanceOf(FileAttributes::class, $attributes);
        self::assertSame(3, $attributes->size());
    }

    public function testAttributesThrowsWhenFileDoesNotExist(): void
    {
        $file = File::createByFullFilenamePathString($this->workDir . '/missing.txt');

        $this->expectException(FileNotFoundException::class);

        $file->attributes();
    }
}
