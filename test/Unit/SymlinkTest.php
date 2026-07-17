<?php

declare(strict_types=1);

namespace Tests\FsKit;

use FsKit\Artifacts\Symlink;
use PHPUnit\Framework\TestCase;

class SymlinkTest extends TestCase
{
    private string $workDir;

    protected function setUp(): void
    {
        $this->workDir = sys_get_temp_dir() . '/fskit-link-test-' . uniqid('', true);
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
        $link = Symlink::createByFullPathString('/some/place/shortcut');

        self::assertSame('/some/place/shortcut', $link->path());
        self::assertSame('shortcut', $link->name());
    }

    public function testExistsIsTrueOnlyForActualSymlinks(): void
    {
        file_put_contents($this->workDir . '/regular.txt', 'x');
        symlink($this->workDir . '/regular.txt', $this->workDir . '/link.txt');

        self::assertTrue(Symlink::createByFullPathString($this->workDir . '/link.txt')->exists());
        self::assertFalse(Symlink::createByFullPathString($this->workDir . '/regular.txt')->exists());
        self::assertFalse(Symlink::createByFullPathString($this->workDir . '/missing')->exists());
    }

    public function testTargetReturnsRawLinkTarget(): void
    {
        file_put_contents($this->workDir . '/target.txt', 'x');
        symlink($this->workDir . '/target.txt', $this->workDir . '/link.txt');

        $link = Symlink::createByFullPathString($this->workDir . '/link.txt');

        self::assertSame($this->workDir . '/target.txt', $link->target());
    }

    public function testTargetIsNullWhenLinkDoesNotExist(): void
    {
        $link = Symlink::createByFullPathString($this->workDir . '/missing');

        self::assertNull($link->target());
    }

    public function testIsBrokenDetectsDanglingLink(): void
    {
        file_put_contents($this->workDir . '/target.txt', 'x');
        symlink($this->workDir . '/target.txt', $this->workDir . '/good_link');
        symlink($this->workDir . '/vanished.txt', $this->workDir . '/bad_link');

        self::assertFalse(Symlink::createByFullPathString($this->workDir . '/good_link')->isBroken());
        self::assertTrue(Symlink::createByFullPathString($this->workDir . '/bad_link')->isBroken());
    }

    public function testBrokenLinkStillExistsAndReportsTarget(): void
    {
        symlink($this->workDir . '/vanished.txt', $this->workDir . '/bad_link');

        $link = Symlink::createByFullPathString($this->workDir . '/bad_link');

        self::assertTrue($link->exists());
        self::assertSame($this->workDir . '/vanished.txt', $link->target());
    }

    public function testParentDirectory(): void
    {
        $link = Symlink::createByFullPathString('/some/place/shortcut');

        self::assertSame('/some/place', $link->parentDirectory()->path());
    }
}
