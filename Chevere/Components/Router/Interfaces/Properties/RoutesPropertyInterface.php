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

namespace Chevere\Components\Router\Interfaces\Properties;

use Chevere\Components\Common\Interfaces\ToArrayInterface;
use Chevere\Components\Router\Exceptions\RouterPropertyException;

interface RoutesPropertyInterface extends ToArrayInterface
{
    /** @var string property name */
    const NAME = 'routes';

    /**
     * Creates a new instance.
     *
     * @param array $routes [(int)$id => RouteInterface]
     *
     * @throws RouterPropertyException if the value doesn't match the property format
     */
    public function __construct(array $routes);
}
