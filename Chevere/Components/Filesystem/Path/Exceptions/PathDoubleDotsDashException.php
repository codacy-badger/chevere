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

namespace Chevere\Components\Filesystem\Path\Exceptions;

use Exception;

/**
 * Exception thrown when the path contains '..'
 */
final class PathDoubleDotsDashException extends Exception
{
}
