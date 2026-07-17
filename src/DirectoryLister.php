<?php

declare(strict_types=1);

namespace FsKit;

use FilesystemIterator;
use FsKit\Artifacts\Symlink;
use FsKit\Contracts\DirectoryInterface;
use FsKit\Contracts\DirectoryListerInterface;
use FsKit\Exceptions\DirectoryNotFoundException;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

final class DirectoryLister implements DirectoryListerInterface
{
    public function ls(DirectoryInterface $directory, bool $recursive = false): DirectoryListing
    {
        if (!$directory->exists()) {
            throw new DirectoryNotFoundException(sprintf('Directory "%s" does not exist.', $directory->path()));
        }

        $iterator = $recursive
            ? new RecursiveIteratorIterator(
                new RecursiveDirectoryIterator($directory->path(), FilesystemIterator::SKIP_DOTS),
                RecursiveIteratorIterator::SELF_FIRST
            )
            : new FilesystemIterator($directory->path(), FilesystemIterator::SKIP_DOTS);

        $entries = [];

        foreach ($iterator as $entry) {
            $entries[] = match (true) {
                $entry->isLink() => Symlink::createByFullPathString($entry->getPathname()),
                $entry->isDir()  => Directory::createByFullPathString($entry->getPathname()),
                default => File::createByFullFilenamePathString($entry->getPathname()),
            };
        }

        return new DirectoryListing(...$entries);
    }
}
