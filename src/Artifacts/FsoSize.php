<?php

declare(strict_types=1);

namespace FsKit\Artifacts;

readonly class FsoSize
{
    public function __construct(
        private int $bytes
    ) {
    }

    public function value(): int
    {
        return $this->getInBytes();
    }

    public function getInBytes(): int
    {
        return $this->bytes ?? 0;
    }

    public function getInKiloBytes(): float
    {
        return $this->bytes / 1024;
    }

    public function getInMegaBytes(): float
    {
        return $this->bytes / (1024 ** 2);
    }

    public function getInGigaBytes(): float
    {
        return $this->bytes / (1024 ** 3);
    }
}
