<?php

declare(strict_types=1);

namespace FsKit\Contracts;

use FsKit\Artifacts\FDType;
use FsKit\Artifacts\FsoSize;
use FsKit\Artifacts\FsoType;

interface FileInterface extends ArtifactInterface
{
    public function extension(): string;

    public function isReadable(): bool;

    public function isWritable(): bool;

    public function attributes(): AttributesInterface;

    public function fsoType(): FsoType;

    public function fdType(): FDType;

    public function size(): null|FsoSize;

    public function read(): string;

    public function touch(): bool;

    public function write(string $contents, bool $append = false): int;

    public function append(string $contents): int;

    public function delete(): bool;

    public function copyTo(string $destinationPath): FileInterface;

    public function moveTo(string $destinationPath): FileInterface;
}
