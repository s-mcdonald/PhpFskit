<?php

declare(strict_types=1);

namespace Tests\FsKit;

use FsKit\Artifacts\FsoSize;
use PHPUnit\Framework\TestCase;

class FsoSizeTest extends TestCase
{
    public function testValueReturnsBytes(): void
    {
        $size = new FsoSize(2048);

        self::assertSame(2048, $size->value());
        self::assertSame(2048, $size->getInBytes());
    }

    public function testZeroBytes(): void
    {
        $size = new FsoSize(0);

        self::assertSame(0, $size->getInBytes());
        self::assertSame(0.0, $size->getInKiloBytes());
        self::assertSame(0.0, $size->getInMegaBytes());
        self::assertSame(0.0, $size->getInGigaBytes());
    }

    public function testUnitConversions(): void
    {
        $size = new FsoSize(3 * 1024 ** 3);

        self::assertSame(3 * 1024 ** 3, $size->getInBytes());
        self::assertSame(3.0 * 1024 ** 2, $size->getInKiloBytes());
        self::assertSame(3.0 * 1024, $size->getInMegaBytes());
        self::assertSame(3.0, $size->getInGigaBytes());
    }

    public function testFractionalConversions(): void
    {
        $size = new FsoSize(1536);

        self::assertSame(1.5, $size->getInKiloBytes());
        self::assertEqualsWithDelta(0.00146484375, $size->getInMegaBytes(), 1.0E-12);
    }
}
