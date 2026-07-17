<?php

declare(strict_types=1);

namespace Tests\FsKit;

use FsKit\Artifacts\FDType;
use FsKit\File;
use PHPUnit\Framework\TestCase;

class FDTypeTest extends TestCase
{
    private const FIXTURES = __DIR__ . '/../Fixtures/fs';

    public function testFromPathClassifiesFile(): void
    {
        self::assertSame(FDType::File, FDType::fromPath(self::FIXTURES . '/file_001.txt'));
    }

    public function testFromPathClassifiesDirectory(): void
    {
        self::assertSame(FDType::Directory, FDType::fromPath(self::FIXTURES));
    }

    public function testFromPathCollapsesSymlinkToFile(): void
    {
        self::assertSame(FDType::File, FDType::fromPath(self::FIXTURES . '/misc/broken_link.txt'));
    }

    public function testFromPathAcceptsFileHandle(): void
    {
        $file = File::createByFullFilenamePathString(self::FIXTURES . '/file_001.txt');

        self::assertSame(FDType::File, FDType::fromPath($file));
    }

    public function testBackedValues(): void
    {
        self::assertSame('file', FDType::File->value);
        self::assertSame('dir', FDType::Directory->value);
        self::assertSame('other', FDType::Other->value);
    }
}
