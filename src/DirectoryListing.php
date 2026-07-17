<?php

declare(strict_types=1);

namespace FsKit;

use ArrayIterator;
use Countable;
use FsKit\Artifacts\Symlink;
use FsKit\Contracts\ArtifactInterface;
use IteratorAggregate;
use Traversable;

/**
 * @implements IteratorAggregate<int, ArtifactInterface>
 */
final readonly class DirectoryListing implements IteratorAggregate, Countable
{
    private array $entries;

    public function __construct(
        ArtifactInterface ...$entries
    ) {
        $this->entries = $entries;
    }

    /** @return File[] */
    public function files(): array
    {
        return array_values(array_filter(
            $this->entries,
            static fn (ArtifactInterface $entry): bool => $entry instanceof File
        ));
    }

    /** @return Directory[] */
    public function directories(): array
    {
        return array_values(array_filter(
            $this->entries,
            static fn (ArtifactInterface $entry): bool => $entry instanceof Directory
        ));
    }

    /** @return Symlink[] */
    public function symlinks(): array
    {
        return array_values(array_filter(
            $this->entries,
            static fn (ArtifactInterface $entry): bool => $entry instanceof Symlink
        ));
    }

    /** @return ArtifactInterface[] */
    public function all(): array
    {
        return $this->entries;
    }

    public function count(): int
    {
        return count($this->entries);
    }

    public function getIterator(): Traversable
    {
        return new ArrayIterator($this->entries);
    }
}
