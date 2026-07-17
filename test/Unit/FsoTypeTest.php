<?php

declare(strict_types=1);

namespace Tests\FsKit;

use FsKit\Artifacts\FsoType;
use FsKit\File;
use PHPUnit\Framework\TestCase;

class FsoTypeTest extends TestCase
{
    private const FIXTURES = __DIR__ . '/../Fixtures/fs';

    public function testFromPathClassifiesFile(): void
    {
        self::assertSame(FsoType::Filename, FsoType::fromPath(self::FIXTURES . '/file_001.txt'));
    }

    public function testFromPathClassifiesDirectory(): void
    {
        self::assertSame(FsoType::Directory, FsoType::fromPath(self::FIXTURES));
    }

    public function testFromPathClassifiesSymlink(): void
    {
        self::assertSame(FsoType::Link, FsoType::fromPath(self::FIXTURES . '/misc/broken_link.txt'));
    }

    public function testFromPathAcceptsFileHandle(): void
    {
        $file = File::createByFullFilenamePathString(self::FIXTURES . '/file_001.txt');

        self::assertSame(FsoType::Filename, FsoType::fromPath($file));
    }

    public function testBackedValues(): void
    {
        self::assertSame('file', FsoType::Filename->value);
        self::assertSame('dir', FsoType::Directory->value);
        self::assertSame('link', FsoType::Link->value);
        self::assertSame('socket', FsoType::Socket->value);
        self::assertSame('char', FsoType::CharacterDevice->value);
        self::assertSame('block', FsoType::BlockDevice->value);
        self::assertSame('other', FsoType::Other->value);
    }
}
