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

namespace Chevere\Components\Cache;

use Chevere\Components\Cache\Exceptions\CacheKeyNotFoundException;
use Chevere\Components\Filesystem\File;
use Chevere\Components\Filesystem\FileCompile;
use Chevere\Components\Filesystem\PhpFile;
use Chevere\Components\Filesystem\FileReturn;
use Chevere\Components\Cache\Interfaces\CacheInterface;
use Chevere\Components\Cache\Interfaces\CacheItemInterface;
use Chevere\Components\Cache\Interfaces\CacheKeyInterface;
use Chevere\Components\Filesystem\Dir\Interfaces\DirInterface;
use Chevere\Components\Filesystem\Path\Interfaces\PathInterface;
use Chevere\Components\Variable\Interfaces\VariableExportInterface;

/**
 * A simple PHP based cache system.
 *
 * Using FileReturn, it provides cache by using php files that return a single variable.
 *
 * cached.php >>> <?php return 'my cached data';
 */
final class Cache implements CacheInterface
{
    /** @var DirInterface */
    private DirInterface $dir;

    /** @var array An array [key => [checksum => , path =>]] containing information about the cache items */
    private array $puts;

    /**
     * Creates a new instance.
     *
     * @param DirInterface $dir the directory where cache files will be stored/accesed (must exists)
     *
     * @throws DirUnableToCreateException if $dir doesn't exists and unable to create
     */
    public function __construct(DirInterface $dir)
    {
        $this->dir = $dir;
        if (!$this->dir->exists()) {
            $this->dir->create();
        }
        $this->puts = [];
    }

    public function withPut(CacheKeyInterface $cacheKey, VariableExportInterface $variableExport): CacheInterface
    {
        $path = $this->getPath($cacheKey->toString());
        $file = new File($path);
        if (!$file->exists()) {
            $file->create();
        }
        $filePhp = new PhpFile($file);
        $fileReturn = new FileReturn($filePhp);
        $fileReturn->put($variableExport);
        new FileCompile($filePhp);
        $new = clone $this;
        $new->puts[$cacheKey->toString()] = [
            'path' => $fileReturn->filePhp()->file()->path()->absolute(),
            'checksum' => $fileReturn->filePhp()->file()->checksum(),
        ];

        return $new;
    }

    public function withRemove(CacheKeyInterface $cacheKey): CacheInterface
    {
        $new = clone $this;
        $path = $this->getPath($cacheKey->toString());
        if (!$path->exists()) {
            return $new;
        }
        $fileCompile =
            new FileCompile(
                new PhpFile(
                    new File($path)
                )
            );
        $fileCompile->destroy();
        $fileCompile->filePhp()->file()->remove();

        unset($new->puts[$cacheKey->toString()]);

        return $new;
    }

    public function exists(CacheKeyInterface $cacheKey): bool
    {
        return $this->getPath($cacheKey->toString())
            ->exists();
    }

    public function get(CacheKeyInterface $cacheKey): CacheItemInterface
    {
        $path = $this->getPath($cacheKey->toString());
        if (!$path->exists()) {
            throw new CacheKeyNotFoundException('No cache for key ' . $cacheKey->toString());
        }

        return new CacheItem(
            new FileReturn(
                new PhpFile(
                    new File($path)
                )
            )
        );
    }

    public function toArray(): array
    {
        return $this->puts;
    }

    private function getPath(string $name): PathInterface
    {
        return $this->dir->path()
            ->getChild($name . '.php');
    }
}
