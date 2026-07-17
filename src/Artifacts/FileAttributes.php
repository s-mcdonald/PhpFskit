<?php

declare(strict_types=1);

namespace FsKit\Artifacts;

use FsKit\Contracts\AttributesInterface;
use FsKit\Exceptions\FileNotFoundException;

readonly class FileAttributes implements AttributesInterface
{
    private string $path;
    private FsoType $type; // real type
    private FDType $fdType; // i.e link is a file
    private null|string $visibility;
    private null|int $dateCreated;
    private null|int $lastModified;
    private null|int $size;
    private null|string $mimeType;

    public function __construct(string $filePath)
    {
        if (!is_file($filePath)) {
            throw new FileNotFoundException(sprintf('File "%s" does not exist.', $filePath));
        }

        $this->path = $filePath;
        $this->type = FsoType::fromPath($filePath);
        $this->fdType = FDType::fromPath($filePath);

        $perms = fileperms($filePath);
        $this->visibility = $perms === false ? null : substr(sprintf('%o', $perms), -4);

        $ctime = filectime($filePath);
        $this->dateCreated = $ctime === false ? null : $ctime;

        $mtime = filemtime($filePath);
        $this->lastModified = $mtime === false ? null : $mtime;

        $size = filesize($filePath);
        $this->size = $size === false ? null : $size;

        $mime = @mime_content_type($filePath);
        $this->mimeType = $mime === false ? null : $mime;
    }

    public function path(): string
    {
        return $this->path;
    }

    public function type(): FsoType
    {
        return $this->type;
    }

    public function fdType(): FDType
    {
        return $this->fdType;
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

    /**
     * @return int|null size in bytes, null if no file exist
     */
    public function size(): null|int
    {
        return $this->size;
    }

    public function mimeType(): null|string
    {
        return $this->mimeType;
    }
}
