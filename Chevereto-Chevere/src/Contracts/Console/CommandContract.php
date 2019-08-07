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

namespace Chevere\Contracts\Console;

use Chevere\Contracts\App\LoaderContract;

interface CommandContract
{
    public function __construct(CliContract $cli);

    /**
     * The actual command code.
     *
     * @param LoaderContract $loader
     */
    public function callback(LoaderContract $loader);
}
