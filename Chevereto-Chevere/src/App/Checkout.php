<?php

declare(strict_types=1);

/*
 * This file is part of Chevere.
 *
 * (c) Rodolfo Berrios <rodolfo@chevereto.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Chevere\App;

use Chevere\FileReturn\FileReturn;

final class Checkout
{
    /** @var Build */
    private $build;

    /** @var FileReturn */
    private $fileReturn;

    public function __construct(Build $build)
    {
        $this->build = $build;
        $this->fileReturn = new FileReturn($this->build->pathHandle());
        $this->fileReturn->put($this->build->cacheChecksums());
    }

    public function build(): Build
    {
        return $this->build;
    }

    public function checksum(): string
    {
        return $this->fileReturn->checksum();
    }
}
