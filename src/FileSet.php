<?php

declare(strict_types=1);

namespace FsKit;

use ArrayIterator;
use Countable;
use FsKit\Contracts\DirectoryInterface;
use FsKit\Contracts\FileInterface;
use IteratorAggregate;
use Traversable;

final class FileSet implements IteratorAggregate, Countable
{
    /** @var DirectoryInterface[] */
    private array $directories = [];

    /** @var FileInterface[] */
    private array $files = [];

    /** @var array<string, true> excluded file paths */
    private array $excludedFiles = [];

    /** @var string[] excluded directory paths */
    private array $excludedDirectories = [];

    private function __construct()
    {
    }

    public static function createFromDir(string|DirectoryInterface $dir): self
    {
        return (new self())->addDir($dir);
    }

    public function addDir(string|DirectoryInterface $dir): self
    {
        $clone = clone $this;
        $clone->directories[] = self::toDirectory($dir);

        return $clone;
    }

    public function addFile(string|FileInterface $file): self
    {
        $clone = clone $this;
        $clone->files[] = self::toFile($file);

        return $clone;
    }

    public function excludeFile(string|FileInterface $file): self
    {
        $clone = clone $this;
        $clone->excludedFiles[self::toFile($file)->path()] = true;

        return $clone;
    }

    public function excludeDirectoryFile(string|DirectoryInterface $dir): self
    {
        $clone = clone $this;
        $clone->excludedDirectories[] = self::toDirectory($dir)->path();

        return $clone;
    }

    /** @return FileInterface[] */
    public function files(): array
    {
        return $this->resolve();
    }

    public function count(): int
    {
        return count($this->resolve());
    }

    public function getIterator(): Traversable
    {
        return new ArrayIterator($this->resolve());
    }

    /**
     * Walks every added directory, merges in explicitly added files
     * (deduplicated by path), then drops excluded entries.
     *
     * @return FileInterface[]
     */
    private function resolve(): array
    {
        $files = [];

        foreach ($this->directories as $directory) {
            foreach ($directory->ls(true)->files() as $file) {
                $files[$file->path()] = $file;
            }
        }

        foreach ($this->files as $file) {
            $files[$file->path()] = $file;
        }

        foreach (array_keys($files) as $path) {
            if ($this->isExcluded($path)) {
                unset($files[$path]);
            }
        }

        return array_values($files);
    }

    private function isExcluded(string $path): bool
    {
        if (isset($this->excludedFiles[$path])) {
            return true;
        }

        foreach ($this->excludedDirectories as $directoryPath) {
            if (str_starts_with($path, $directoryPath . DIRECTORY_SEPARATOR)) {
                return true;
            }
        }

        return false;
    }

    private static function toDirectory(string|DirectoryInterface $dir): DirectoryInterface
    {
        return $dir instanceof DirectoryInterface ? $dir : Directory::createByFullPathString($dir);
    }

    private static function toFile(string|FileInterface $file): FileInterface
    {
        return $file instanceof FileInterface ? $file : File::createByFullFilenamePathString($file);
    }
}
