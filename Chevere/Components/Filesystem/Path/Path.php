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

namespace Chevere\Components\Filesystem\Path;

use Chevere\Components\Filesystem\Dir\Exceptions\PathUnableToChmodException;
use Chevere\Components\Filesystem\Path\Exceptions\PathDoesntExistsException;
use Chevere\Components\Message\Message;
use Chevere\Components\Filesystem\Path\Exceptions\PathInvalidException;
use Chevere\Components\Filesystem\Path\Interfaces\PathInterface;
use function ChevereFn\stringStartsWith;

/**
 * Handles paths with context.
 */
class Path implements PathInterface
{
    /** @var string Absolute path */
    private string $absolute;

    public function __construct(string $absolute)
    {
        new PathFormat($absolute);
        $this->absolute = $absolute;
    }

    public function absolute(): string
    {
        return $this->absolute;
    }

    public function exists(): bool
    {
        $this->clearStatCache();

        return false !== stream_resolve_include_path($this->absolute);
    }

    public function isDir(): bool
    {
        $this->clearStatCache();

        return is_dir($this->absolute);
    }

    public function isFile(): bool
    {
        $this->clearStatCache();

        return is_file($this->absolute);
    }

    public function chmod(int $mode): void
    {
        $this->assertExists();
        if (chmod($this->absolute, $mode) === false) {
            throw new PathUnableToChmodException(
                (new Message('Unable to chmod %mode% %path%'))
                    ->strong('%mode%', $mode)
                    ->code('%path%', $this->absolute)
                    ->toString()
            );
        }
    }

    public function isWriteable(): bool
    {
        $this->assertExists();

        return is_writable($this->absolute);
    }

    public function isReadable(): bool
    {
        $this->assertExists();

        return is_readable($this->absolute);
    }

    public function getChild(string $path): PathInterface
    {
        $parent = $this->absolute;
        $childrenPath = rtrim($parent, '/');

        return new Path($childrenPath . '/' . $path);
    }

    private function assertExists(): void
    {
        if (!$this->exists()) {
            throw new PathDoesntExistsException(
                (new Message("Path %path% doesn't exists"))
                    ->code('%path%', $this->absolute)
                    ->toString()
            );
        }
    }

    private function clearStatCache(): void
    {
        clearstatcache(true, $this->absolute);
    }
}
