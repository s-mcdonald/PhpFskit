<?php

declare(strict_types=1);

namespace Tests\FsKit;

use FsKit\Artifacts\Symlink;
use FsKit\Directory;
use FsKit\DirectoryListing;
use FsKit\File;
use PHPUnit\Framework\TestCase;

class DirectoryListingTest extends TestCase
{
    private function makeListing(): DirectoryListing
    {
        return new DirectoryListing(
            File::createByFullFilenamePathString('/tree/a.txt'),
            Directory::createByFullPathString('/tree/sub'),
            Symlink::createByFullPathString('/tree/shortcut'),
            File::createByFullFilenamePathString('/tree/b.txt'),
        );
    }

    public function testEmptyListing(): void
    {
        $listing = new DirectoryListing();

        self::assertCount(0, $listing);
        self::assertSame([], $listing->all());
        self::assertSame([], $listing->files());
        self::assertSame([], $listing->directories());
        self::assertSame([], $listing->symlinks());
    }

    public function testCountAndAll(): void
    {
        $listing = $this->makeListing();

        self::assertCount(4, $listing);
        self::assertCount(4, $listing->all());
    }

    public function testFilesFilterKeepsOnlyFilesWithReindexedKeys(): void
    {
        $files = $this->makeListing()->files();

        self::assertSame([0, 1], array_keys($files));
        self::assertContainsOnlyInstancesOf(File::class, $files);
        self::assertSame('a.txt', $files[0]->name());
        self::assertSame('b.txt', $files[1]->name());
    }

    public function testDirectoriesFilter(): void
    {
        $directories = $this->makeListing()->directories();

        self::assertCount(1, $directories);
        self::assertContainsOnlyInstancesOf(Directory::class, $directories);
        self::assertSame('sub', $directories[0]->name());
    }

    public function testSymlinksFilter(): void
    {
        $symlinks = $this->makeListing()->symlinks();

        self::assertCount(1, $symlinks);
        self::assertContainsOnlyInstancesOf(Symlink::class, $symlinks);
        self::assertSame('shortcut', $symlinks[0]->name());
    }

    public function testIsIterable(): void
    {
        $names = [];

        foreach ($this->makeListing() as $entry) {
            $names[] = $entry->name();
        }

        self::assertSame(['a.txt', 'sub', 'shortcut', 'b.txt'], $names);
    }
}
