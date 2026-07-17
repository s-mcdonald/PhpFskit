<?php

declare(strict_types=1);

namespace FsKit\Artifacts;

use FsKit\Contracts\SymlinkInterface;
use FsKit\Directory;

readonly class Symlink implements SymlinkInterface
{
    private function __construct(
        private string $linkPath
    ) {
    }

    public static function createByFullPathString(string $linkPath): self
    {
        return new self($linkPath);
    }

    public function path(): string
    {
        return $this->linkPath;
    }

    public function name(): string
    {
        return pathinfo($this->linkPath, PATHINFO_BASENAME);
    }

    public function exists(): bool
    {
        return is_link($this->linkPath);
    }

    /**
     * The raw target the link points at, or null if it can't be resolved.
     */
    public function target(): null|string
    {
        if (!$this->exists()) {
            return null;
        }

        $target = @readlink($this->linkPath);

        return $target === false ? null : $target;
    }

    /**
     * True when the link exists but its target does not.
     */
    public function isBroken(): bool
    {
        return $this->exists() && !file_exists($this->linkPath);
    }

    public function parentDirectory(): Directory
    {
        return Directory::createByFullPathString(pathinfo($this->linkPath, PATHINFO_DIRNAME));
    }
}
