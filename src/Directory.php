<?php

declare(strict_types=1);

namespace FsKit;

use FsKit\Artifacts\DirectoryAttributes;
use FsKit\Contracts\DirectoryInterface;
use FsKit\Exceptions\DirectoryNotFoundException;
use FsKit\Exceptions\FileSystemIOException;

final readonly class Directory implements DirectoryInterface
{
    private function __construct(
        private string $directoryPath
    ) {
    }

    public static function createByFullPathString(string $directoryPath): self
    {
        return new self(rtrim($directoryPath, '/\\') ?: $directoryPath);
    }

    public function path(): string
    {
        return $this->directoryPath;
    }

    public function name(): string
    {
        return pathinfo($this->directoryPath, PATHINFO_BASENAME);
    }

    public function exists(): bool
    {
        return is_dir($this->directoryPath);
    }

    public function attributes(): DirectoryAttributes
    {
        return new DirectoryAttributes($this->directoryPath);
    }

    public function create(int $permissions = 0755, bool $recursive = true): bool
    {
        if ($this->exists()) {
            return true;
        }

        if (!mkdir($concurrentDirectory = $this->directoryPath, $permissions, $recursive) && !is_dir($concurrentDirectory)) {
            throw new FileSystemIOException(sprintf('Unable to create directory "%s".', $this->directoryPath));
        }

        return true;
    }

    public function delete(): bool
    {
        $this->assertExists();

        if (!@rmdir($this->directoryPath)) {
            throw new FileSystemIOException(sprintf('Unable to delete directory "%s" (is it empty?).', $this->directoryPath));
        }

        return true;
    }

    public function file(string $filename): File
    {
        return File::createByFullFilenamePathString(
            $this->directoryPath . DIRECTORY_SEPARATOR . $filename
        );
    }

    public function subdirectory(string $name): self
    {
        return self::createByFullPathString(
            $this->directoryPath . DIRECTORY_SEPARATOR . $name
        );
    }

    public function parentDirectory(): self
    {
        return self::createByFullPathString(pathinfo($this->directoryPath, PATHINFO_DIRNAME));
    }

    /**
     * Lists the directory's contents as File/Directory objects.
     *
     * @param bool $recursive walk subdirectories too, depth-first
     */
    public function ls(bool $recursive = false): DirectoryListing
    {
        return (new DirectoryLister())->ls($this, $recursive);
    }

    private function assertExists(): void
    {
        if (!$this->exists()) {
            throw new DirectoryNotFoundException(sprintf('Directory "%s" does not exist.', $this->directoryPath));
        }
    }
}
