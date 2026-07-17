<?php

declare(strict_types=1);

namespace FsKit\Artifacts;

use FsKit\File;

enum FsoType: string
{
    case Filename = 'file';
    case Directory = 'dir';
    case Link = 'link';
    case Socket = 'socket';
    case CharacterDevice = 'char';
    case BlockDevice = 'block';
    case Other = 'other';

    public static function fromPath(string|File $filePath): self
    {
        if ($filePath instanceof File) {
            $filePath = $filePath->path();
        }

        return match(filetype($filePath)) {
            'file' => self::Filename,
            'dir' => self::Directory,
            'link' => self::Link,
            'socket' => self::Socket,
            'char' => self::CharacterDevice,
            'block' => self::BlockDevice,
            default => self::Other,
        };
    }
}
