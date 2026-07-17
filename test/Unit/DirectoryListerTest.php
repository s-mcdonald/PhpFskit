<?php

declare(strict_types=1);

namespace Tests\FsKit;

use FsKit\Artifacts\Symlink;
use FsKit\Directory;
use FsKit\DirectoryLister;
use FsKit\Exceptions\DirectoryNotFoundException;
use FsKit\File;
use PHPUnit\Framework\TestCase;

class DirectoryListerTest extends TestCase
{
    private string $workDir;

    protected function setUp(): void
    {
        $this->workDir = sys_get_temp_dir() . '/fskit-lister-test-' . uniqid('', true);

        mkdir($this->workDir . '/sub/deeper', 0755, true);
        file_put_contents($this->workDir . '/root_a.txt', 'a');
        file_put_contents($this->workDir . '/root_b.txt', 'b');
        file_put_contents($this->workDir . '/sub/nested.txt', 'n');
        file_put_contents($this->workDir . '/sub/deeper/deepest.txt', 'd');
        symlink($this->workDir . '/root_a.txt', $this->workDir . '/shortcut');
    }

    protected function tearDown(): void
    {
        if (is_dir($this->workDir)) {
            exec('rm -rf ' . escapeshellarg($this->workDir));
        }
    }

    public function testThrowsWhenDirectoryDoesNotExist(): void
    {
        $this->expectException(DirectoryNotFoundException::class);

        (new DirectoryLister())->ls(Directory::createByFullPathString($this->workDir . '/missing'));
    }

    public function testNonRecursiveListingClassifiesEntries(): void
    {
        $listing = (new DirectoryLister())->ls(Directory::createByFullPathString($this->workDir));

        self::assertCount(4, $listing);
        self::assertCount(2, $listing->files());
        self::assertCount(1, $listing->directories());
        self::assertCount(1, $listing->symlinks());

        $fileNames = array_map(static fn (File $f): string => $f->name(), $listing->files());
        sort($fileNames);
        self::assertSame(['root_a.txt', 'root_b.txt'], $fileNames);

        self::assertSame('sub', $listing->directories()[0]->name());
        self::assertSame('shortcut', $listing->symlinks()[0]->name());
    }

    public function testSymlinkIsClassifiedAsSymlinkNotFile(): void
    {
        $listing = (new DirectoryLister())->ls(Directory::createByFullPathString($this->workDir));

        foreach ($listing->files() as $file) {
            self::assertNotSame('shortcut', $file->name());
        }

        self::assertInstanceOf(Symlink::class, $listing->symlinks()[0]);
    }

    public function testRecursiveListingWalksSubdirectories(): void
    {
        $listing = (new DirectoryLister())->ls(Directory::createByFullPathString($this->workDir), true);

        $names = array_map(
            static fn ($entry): string => $entry->name(),
            $listing->all()
        );
        sort($names);

        self::assertSame(
            ['deeper', 'deepest.txt', 'nested.txt', 'root_a.txt', 'root_b.txt', 'shortcut', 'sub'],
            $names
        );

        self::assertCount(4, $listing->files());
        self::assertCount(2, $listing->directories());
        self::assertCount(1, $listing->symlinks());
    }

    public function testListingAnEmptyDirectory(): void
    {
        mkdir($this->workDir . '/empty');

        $listing = (new DirectoryLister())->ls(Directory::createByFullPathString($this->workDir . '/empty'));

        self::assertCount(0, $listing);
        self::assertSame([], $listing->all());
    }
}
