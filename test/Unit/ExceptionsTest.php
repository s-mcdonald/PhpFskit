<?php

declare(strict_types=1);

namespace Tests\FsKit;

use FsKit\Exceptions\DirectoryNotFoundException;
use FsKit\Exceptions\FileNotFoundException;
use FsKit\Exceptions\FileSystemException;
use FsKit\Exceptions\FileSystemIOException;
use PHPUnit\Framework\TestCase;
use RuntimeException;

class ExceptionsTest extends TestCase
{
    public function testAllExceptionsExtendFileSystemException(): void
    {
        self::assertInstanceOf(FileSystemException::class, new FileNotFoundException('x'));
        self::assertInstanceOf(FileSystemException::class, new DirectoryNotFoundException('x'));
        self::assertInstanceOf(FileSystemException::class, new FileSystemIOException('x'));
    }

    public function testFileSystemExceptionIsARuntimeException(): void
    {
        self::assertInstanceOf(RuntimeException::class, new FileSystemException('x'));
    }
}
