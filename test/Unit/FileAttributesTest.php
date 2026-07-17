<?php

declare(strict_types=1);

namespace Tests\FsKit;

use FsKit\Artifacts\FDType;
use FsKit\Artifacts\FileAttributes;
use FsKit\Artifacts\FsoType;
use FsKit\Exceptions\FileNotFoundException;
use PHPUnit\Framework\TestCase;

class FileAttributesTest extends TestCase
{
    private string $workDir;

    protected function setUp(): void
    {
        $this->workDir = sys_get_temp_dir() . '/fskit-fattr-test-' . uniqid('', true);
        mkdir($this->workDir, 0755, true);
    }

    protected function tearDown(): void
    {
        if (is_dir($this->workDir)) {
            exec('rm -rf ' . escapeshellarg($this->workDir));
        }
    }

    public function testConstructorThrowsWhenFileDoesNotExist(): void
    {
        $this->expectException(FileNotFoundException::class);

        new FileAttributes($this->workDir . '/missing.txt');
    }

    public function testSnapshotFields(): void
    {
        $path = $this->workDir . '/snapshot.txt';
        file_put_contents($path, 'hello');
        chmod($path, 0644);

        $attributes = new FileAttributes($path);

        self::assertSame($path, $attributes->path());
        self::assertSame(FsoType::Filename, $attributes->type());
        self::assertSame(FDType::File, $attributes->fdType());
        self::assertSame('0644', $attributes->visibility());
        self::assertSame(5, $attributes->size());
        self::assertSame('text/plain', $attributes->mimeType());
        self::assertIsInt($attributes->dateCreated());
        self::assertIsInt($attributes->lastModified());
    }

    public function testSnapshotIsPointInTimeNotLive(): void
    {
        $path = $this->workDir . '/frozen.txt';
        file_put_contents($path, 'aaa');

        $attributes = new FileAttributes($path);

        file_put_contents($path, 'aaaaaaaaaa');

        self::assertSame(3, $attributes->size());
    }
}
