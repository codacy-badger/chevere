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

use Chevere\Components\Cache\Interfaces\CacheItemInterface;
use Chevere\Components\Filesystem\Interfaces\FileReturnInterface;

final class CacheItem implements CacheItemInterface
{
    private FileReturnInterface $fileReturn;

    /**
     * Creates a new instance.
     */
    public function __construct(FileReturnInterface $fileReturn)
    {
        $this->fileReturn = $fileReturn;
    }

    public function raw()
    {
        return $this->fileReturn->raw();
    }

    public function var()
    {
        return $this->fileReturn->var();
    }
}
