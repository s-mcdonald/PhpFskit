<?php

declare(strict_types=1);

namespace FsKit\Artifacts;

use FsKit\File;

enum FDType: string
{
    case File = 'file';

    case Directory = 'dir';

    case Other = 'other';

    public static function fromPath(string|File $filePath): self
    {
        if ($filePath instanceof File) {
            $filePath = $filePath->path();
        }

        return match(filetype($filePath)) {
            'file', 'link' => self::File,
            'dir' => self::Directory,
            default => self::Other,
        };
    }
}
