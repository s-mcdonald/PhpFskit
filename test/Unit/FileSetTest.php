<?php

declare(strict_types=1);

namespace Tests\FsKit;

use FsKit\Directory;
use FsKit\Exceptions\DirectoryNotFoundException;
use FsKit\File;
use FsKit\FileSet;
use PHPUnit\Framework\TestCase;

class FileSetTest extends TestCase
{
    private string $workDir;

    protected function setUp(): void
    {
        $this->workDir = sys_get_temp_dir() . '/fskit-fileset-test-' . uniqid('', true);

        mkdir($this->workDir . '/a/sub', 0755, true);
        mkdir($this->workDir . '/b', 0755, true);

        file_put_contents($this->workDir . '/a/one.txt', 'one');
        file_put_contents($this->workDir . '/a/sub/two.txt', 'two');
        file_put_contents($this->workDir . '/b/three.txt', 'three');
    }

    protected function tearDown(): void
    {
        if (is_dir($this->workDir)) {
            exec('rm -rf ' . escapeshellarg($this->workDir));
        }
    }

    public function testCreateFromDirCollectsFilesRecursively(): void
    {
        $fileSet = FileSet::createFromDir($this->workDir . '/a');

        self::assertSame(
            [
                $this->workDir . '/a/one.txt',
                $this->workDir . '/a/sub/two.txt',
            ],
            $this->sortedPaths($fileSet)
        );
    }

    public function testCreateFromDirAcceptsADirectoryHandle(): void
    {
        $fileSet = FileSet::createFromDir(Directory::createByFullPathString($this->workDir . '/b'));

        self::assertSame([$this->workDir . '/b/three.txt'], $this->sortedPaths($fileSet));
    }

    public function testAddDirMergesAnotherDirectory(): void
    {
        $fileSet = FileSet::createFromDir($this->workDir . '/a')
            ->addDir($this->workDir . '/b');

        self::assertCount(3, $fileSet);
    }

    public function testOverlappingDirectoriesAreDeduplicatedByPath(): void
    {
        $fileSet = FileSet::createFromDir($this->workDir . '/a')
            ->addDir($this->workDir . '/a/sub');

        self::assertSame(
            [
                $this->workDir . '/a/one.txt',
                $this->workDir . '/a/sub/two.txt',
            ],
            $this->sortedPaths($fileSet)
        );
    }

    public function testAddFileAddsAnIndividualFile(): void
    {
        $fileSet = FileSet::createFromDir($this->workDir . '/a')
            ->addFile($this->workDir . '/b/three.txt')
            ->addFile(File::createByFullFilenamePathString($this->workDir . '/b/three.txt'));

        self::assertCount(3, $fileSet);
        self::assertContains($this->workDir . '/b/three.txt', $this->sortedPaths($fileSet));
    }

    public function testExcludeFileRemovesAFile(): void
    {
        $fileSet = FileSet::createFromDir($this->workDir . '/a')
            ->excludeFile($this->workDir . '/a/one.txt');

        self::assertSame([$this->workDir . '/a/sub/two.txt'], $this->sortedPaths($fileSet));
    }

    public function testExcludeDirectoryFileRemovesFilesUnderThatDirectory(): void
    {
        $fileSet = FileSet::createFromDir($this->workDir . '/a')
            ->addDir($this->workDir . '/b')
            ->excludeDirectoryFile($this->workDir . '/a/sub');

        self::assertSame(
            [
                $this->workDir . '/a/one.txt',
                $this->workDir . '/b/three.txt',
            ],
            $this->sortedPaths($fileSet)
        );
    }

    public function testIterationYieldsFileHandles(): void
    {
        foreach (FileSet::createFromDir($this->workDir . '/a') as $file) {
            self::assertInstanceOf(File::class, $file);
            self::assertTrue($file->exists());
        }
    }

    public function testBuildingTheSetIsImmutable(): void
    {
        $original = FileSet::createFromDir($this->workDir . '/a');
        $extended = $original->addDir($this->workDir . '/b');

        self::assertCount(2, $original);
        self::assertCount(3, $extended);
    }

    public function testBuildingNeverTouchesDiskButIterationDoes(): void
    {
        $fileSet = FileSet::createFromDir($this->workDir . '/missing');

        $this->expectException(DirectoryNotFoundException::class);

        iterator_to_array($fileSet);
    }

    /** @return string[] */
    private function sortedPaths(FileSet $fileSet): array
    {
        $paths = array_map(
            static fn (File $file): string => $file->path(),
            iterator_to_array($fileSet)
        );

        sort($paths);

        return $paths;
    }
}
