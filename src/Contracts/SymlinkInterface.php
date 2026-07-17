<?php

declare(strict_types=1);

namespace FsKit\Contracts;

interface SymlinkInterface extends ArtifactInterface
{
    public function target(): null|string;
    public function isBroken(): bool;
}
