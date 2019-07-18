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

namespace Chevereto\Chevere\Controllers;

use const Chevereto\Chevere\CLI;
use Chevereto\Chevere\Console;
use Chevereto\Chevere\Controller;

class Head extends Controller
{
    const OPTIONS = [
        'description' => 'GETT without message-body.',
    ];

    /**
     * Head takes the URI and invokes GET.
     */
    public function __invoke()
    {
        $route = $this->getApp()->route;
        $methodCallabe = $route->getMethodCallable('GET');
        if ($methodCallabe) {
            $this->invoke($methodCallabe);
            $this->getResponse()->unsetContent();
            if (CLI) {
                Console::io()->block($this->getResponse()->getStatusString(), 'STATUS', 'fg=black;bg=green', ' ', true);
            }
        }
    }
}
