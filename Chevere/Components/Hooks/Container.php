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

namespace Chevere\Components\Hooks;

use Chevere\Components\Filesystem\Path\AppPath;

/**
 * A container for the registered hooks.
 */
final class Container
{
    /** @var array */
    private array $array;

    public function __construct()
    {
        $this->array = [] ?? include (new AppPath('var/hooks/registered.php'))->absolute();
    }

    public function getAnchor(object $that, string $anchor): array
    {
        return $this->array[get_class($that)][$anchor] ?? [];
    }
}
