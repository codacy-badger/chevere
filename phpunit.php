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
use Chevere\Components\App\Instances\ScreenContainerInstance;
use Chevere\Components\Bootstrap\Bootstrap;
use Chevere\Components\Filesystem\Dir\Dir;
use Chevere\Components\Filesystem\Path\Path;
use Chevere\Components\Screen\Container;
use Chevere\Components\Screen\Formatters\ConsoleFormatter;
use Chevere\Components\Screen\Formatters\DebugFormatter;
use Chevere\Components\Screen\Formatters\RuntimeFormatter;
use Chevere\Components\Screen\Interfaces\ContainerInterface;
use Chevere\Components\Screen\RuntimeScreen;
use Chevere\Components\Screen\Screen;
use Chevere\Components\Screen\ScreenContainer;

require 'vendor/autoload.php';

$rootDir = new Dir(new Path(__DIR__ . '/Chevere/TestApp'));

new BootstrapInstance(
    (new Bootstrap($rootDir, $rootDir->getChild('app')))
        ->withCli(true)
        ->withDev(false)
);

new ScreenContainerInstance(
    new ScreenContainer(
        (new Container(
            new Screen(false, new RuntimeFormatter)
        ))
            ->withDebugScreen(
                new Screen(false, new DebugFormatter)
            )
            ->withConsoleScreen(
                new Screen(false, new DebugFormatter)
            )
    )
);
