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

use Chevere\Components\App\Instances\BootstrapInstance;
use Chevere\Components\Filesystem\Dir\Interfaces\DirInterface;
use Chevere\Components\Message\Message;
use Chevere\Components\Filesystem\Path\Exceptions\PathNotAllowedException;
use Chevere\Components\Filesystem\Path\Interfaces\PathFormatInterface;
use Chevere\Components\Filesystem\Path\Interfaces\AppPathInterface;
use Chevere\Components\Filesystem\Path\Interfaces\PathInterface;
use function ChevereFn\stringForwardSlashes;
use function ChevereFn\stringReplaceFirst;
use function ChevereFn\stringStartsWith;

/**
 * A proxy class to handle paths in the application context.
 */
class AppPath implements AppPathInterface
{
    // private CheckFormatInterface $checkFormat;

    /** @var string Relative path passed on instance construct */
    private string $path;

    /** @var DirInterface Root dir context */
    private DirInterface $rootDir;

    private PathInterface $pathContext;

    /** @var string Absolute path */
    private string $absolute;

    /** @var string Relative path (to project root) */
    private string $relative;

    /**
     * Construct a new instance.
     */
    public function __construct(string $path)
    {
        $this->path = $path;
        $this->relative = $path;
        $this->rootDir = BootstrapInstance::get()->appDir();
        $this->handleRelative();
        $this->pathContext = $this->rootDir->path()->getChild($this->relative);
    }

    public function absolute(): string
    {
        return $this->pathContext->absolute();
    }

    public function relative(): string
    {
        return $this->relative;
    }

    public function exists(): bool
    {
        return $this->pathContext->exists();
    }

    public function isDir(): bool
    {
        return $this->pathContext->isDir();
    }

    public function isFile(): bool
    {
        return $this->pathContext->isFile();
    }

    public function chmod(int $mode): void
    {
        $this->pathContext->chmod($mode);
    }

    public function isWriteable(): bool
    {
        return $this->pathContext->isWriteable();
    }

    public function isReadable(): bool
    {
        return $this->pathContext->isReadable();
    }

    public function getChild(string $path): PathInterface
    {
        return $this->pathContext->getChild($path);
    }

    private function handleRelative(): void
    {
        if (stringStartsWith('/', $this->path)) {
            $this->assertAbsolutePath();
            $this->relative = ltrim(stringReplaceFirst($this->rootDir->path()->absolute(), '', $this->path), '/');
        }
    }

    private function assertAbsolutePath(): void
    {
        if (!stringStartsWith($this->rootDir->path()->absolute(), $this->path)) {
            throw new PathNotAllowedException(
                (new Message('Only absolute paths in the app path %root% are allowed, path %path% provided'))
                    ->code('%root%', $this->rootDir->path()->absolute())
                    ->code('%path%', $this->path)
                    ->toString()
            );
        }
    }
}
