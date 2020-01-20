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

namespace Chevere;

use Chevere\Components\App\Instances\BootstrapInstance;
use Chevere\Components\Bootstrap\Bootstrap;

require dirname(__DIR__) . '/vendor/autoload.php';

new BootstrapInstance(
    (new Bootstrap(__DIR__ . '/Chevere/TestApp/'))
        ->withCli(true)
        ->withConsole(false)
        ->withDev(false)
        ->withAppAutoloader('Chevere\\TestApp\\App')
);
