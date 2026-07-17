<?php

declare(strict_types=1);

namespace FsKit;

use FsKit\Artifacts\FDType;
use FsKit\Artifacts\FileAttributes;
use FsKit\Artifacts\FsoSize;
use FsKit\Artifacts\FsoType;
use FsKit\Contracts\FileInterface;
use FsKit\Exceptions\FileNotFoundException;
use FsKit\Exceptions\FileSystemIOException;

final readonly class File implements FileInterface
{
    private function __construct(
        private string $filePath
    ) {
    }

    public static function createByFullFilenamePathString(string $filePath): self
    {
        return new self($filePath);
    }

    public function path(): string
    {
        return $this->filePath;
    }

    public function name(): string
    {
        return pathinfo($this->filePath, PATHINFO_BASENAME);
    }

    public function extension(): string
    {
        return pathinfo($this->filePath, PATHINFO_EXTENSION);
    }

    public function exists(): bool
    {
        return is_file($this->filePath);
    }

    public function isReadable(): bool
    {
        return is_readable($this->filePath);
    }

    public function isWritable(): bool
    {
        return is_writable($this->filePath);
    }

    public function attributes(): FileAttributes
    {
        return new FileAttributes($this->filePath);
    }

    public function fsoType(): FsoType
    {
        $this->assertExists();

        return FsoType::fromPath($this);
    }

    public function fdType(): FDType
    {
        $this->assertExists();

        return FDType::fromPath($this);
    }

    public function size(): null|FsoSize
    {
        if (!$this->exists()) {
            return null;
        }

        $size = @filesize($this->filePath);

        return new FsoSize($size === false ? 0 : $size);
    }

    public function read(): string
    {
        $this->assertExists();

        $contents = @file_get_contents($this->filePath);

        if ($contents === false) {
            throw new FileSystemIOException(sprintf('Unable to read file "%s".', $this->filePath));
        }

        return $contents;
    }

    public function touch(): bool
    {
        if (!@touch($this->filePath)) {
            throw new FileSystemIOException(sprintf('Unable to touch file "%s".', $this->filePath));
        }

        return true;
    }

    public function append(string $contents): int
    {
        return $this->write($contents, true);
    }

    public function write(string $contents, bool $append = false): int
    {
        $flags = $append ? FILE_APPEND : 0;

        $bytes = @file_put_contents($this->filePath, $contents, $flags);

        if ($bytes === false) {
            throw new FileSystemIOException(sprintf('Unable to write to file "%s".', $this->filePath));
        }

        return $bytes;
    }

    public function delete(): bool
    {
        $this->assertExists();

        if (!@unlink($this->filePath)) {
            throw new FileSystemIOException(sprintf('Unable to delete file "%s".', $this->filePath));
        }

        return true;
    }

    public function copyTo(string $destinationPath): self
    {
        $this->assertExists();

        if (!@copy($this->filePath, $destinationPath)) {
            throw new FileSystemIOException(sprintf('Unable to copy file "%s" to "%s".', $this->filePath, $destinationPath));
        }

        return self::createByFullFilenamePathString($destinationPath);
    }

    public function moveTo(string $destinationPath): self
    {
        $this->assertExists();

        if (!@rename($this->filePath, $destinationPath)) {
            throw new FileSystemIOException(sprintf('Unable to move file "%s" to "%s".', $this->filePath, $destinationPath));
        }

        return self::createByFullFilenamePathString($destinationPath);
    }

    public function parentDirectory(): Directory
    {
        return Directory::createByFullPathString(pathinfo($this->filePath, PATHINFO_DIRNAME));
    }

    private function assertExists(): void
    {
        if (!$this->exists()) {
            throw new FileNotFoundException(sprintf('File "%s" does not exist.', $this->filePath));
        }
    }
}
