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

namespace App;

use Chevere\Components\Controller\ControllerName;
use Chevere\Components\Http\Method;
use Chevere\Components\Route\PathUri;
use Chevere\Components\Route\Route;

return [
    (new Route(new PathUri('/dashboard/{algo}')))
        ->withAddedMethod(
            new Method('GET'),
            new ControllerName(Controllers\Dashboard::class)
        ),
    (new Route(new PathUri('/dashboard/{algo}/{sub}')))
        ->withAddedMethod(
            new Method('GET'),
            new ControllerName(Controllers\Dashboard::class)
        ),
];
