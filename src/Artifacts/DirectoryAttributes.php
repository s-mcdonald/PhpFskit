<?php

declare(strict_types=1);

namespace FsKit\Artifacts;

use FsKit\Contracts\AttributesInterface;
use FsKit\Exceptions\DirectoryNotFoundException;
use function sprintf;

readonly class DirectoryAttributes implements AttributesInterface
{
    private string $path;
    private null|string $visibility;
    private null|int $dateCreated;
    private null|int $lastModified;
    private int $itemCount;

    public function __construct(string $directoryPath)
    {
        if (!is_dir($directoryPath)) {
            throw new DirectoryNotFoundException(sprintf('Directory "%s" does not exist.', $directoryPath));
        }

        $this->path = $directoryPath;

        $perms = @fileperms($directoryPath);
        $this->visibility = $perms === false ? null : substr(sprintf('%o', $perms), -4);

        $ctime = filectime($directoryPath);
        $this->dateCreated = $ctime === false ? null : $ctime;

        $mtime = filemtime($directoryPath);
        $this->lastModified = $mtime === false ? null : $mtime;

        $entries = scandir($directoryPath) ?: [];
        $this->itemCount = count(array_diff($entries, ['.', '..']));
    }

    public function path(): string
    {
        return $this->path;
    }

    public function type(): FsoType
    {
        return FsoType::Directory;
    }

    public function fdType(): FDType
    {
        return FDType::Directory;
    }

    public function visibility(): null|string
    {
        return $this->visibility;
    }

    public function dateCreated(): null|int
    {
        return $this->dateCreated;
    }

    public function lastModified(): null|int
    {
        return $this->lastModified;
    }

    public function itemCount(): int
    {
        return $this->itemCount;
    }
}
