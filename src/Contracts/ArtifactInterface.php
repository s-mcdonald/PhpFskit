<?php

declare(strict_types=1);

namespace FsKit\Contracts;


interface ArtifactInterface
{
    public function path(): string;

    public function name(): string;

    public function exists(): bool;

    public function parentDirectory(): DirectoryInterface;
}
