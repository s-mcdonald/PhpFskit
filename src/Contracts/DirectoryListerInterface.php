<?php

declare(strict_types=1);

namespace FsKit\Contracts;

use FsKit\DirectoryListing;

interface DirectoryListerInterface
{
    public function ls(DirectoryInterface $directory, bool $recursive = false): DirectoryListing;
}
