<?php

/*
 * This file is part of Chevere.
 *
 * (c) Rodolfo Berrios <rodolfo@chevere.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Chevere\Components\Filesystem\Path\Tests;

use Chevere\Components\Filesystem\Path\PathFormat;
use Chevere\Components\Filesystem\Path\Exceptions\PathDotSlashException;
use Chevere\Components\Filesystem\Path\Exceptions\PathDoubleDotsDashException;
use Chevere\Components\Filesystem\Path\Exceptions\PathExtraSlashesException;
use Chevere\Components\Filesystem\Path\Exceptions\PathInvalidException;
use Chevere\Components\Filesystem\Path\Exceptions\PathNotAbsoluteException;
use PHPUnit\Framework\TestCase;

final class PathFormatTest extends TestCase
{
    public function testNoAbsolutePath(): void
    {
        $this->expectException(PathNotAbsoluteException::class);
        (new PathFormat('path'));
    }

    public function testExtraSlashesPath(): void
    {
        $this->expectException(PathExtraSlashesException::class);
        new PathFormat('/some//dir');
    }

    public function testDotSlashPath(): void
    {
        $this->expectException(PathDotSlashException::class);
        new PathFormat('/some/./dir');
    }

    public function testDotsSlashPath(): void
    {
        $this->expectException(PathDoubleDotsDashException::class);
        new PathFormat('/some/../dir');
    }

    public function testConstruct(): void
    {
        $this->expectNotToPerformAssertions();
        (new PathFormat('/path'));
    }
}
