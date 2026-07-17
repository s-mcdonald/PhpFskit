<?php

declare(strict_types=1);

namespace FsKit\Contracts;

use FsKit\DirectoryListing;

interface DirectoryInterface extends ArtifactInterface
{
    public function attributes(): AttributesInterface;

    public function create(int $permissions = 0755, bool $recursive = true): bool;

    public function delete(): bool;

    public function file(string $filename): FileInterface;

    public function subdirectory(string $name): DirectoryInterface;

    public function ls(bool $recursive = false): DirectoryListing;
}
