<?php

/*
 * This file is part of Chevere.
 *
 * (c) Rodolfo Berrios <rodolfo@chevereto.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Chevere\Contracts\Middleware;

interface MiddlewareNamesContract
{
    /**
     * Creates a new instance.
     */
    public function __construct();

    /**
     * Return an instance with the specified middleware name.
     *
     * This method MUST retain the state of the current instance, and return
     * an instance that contains the specified middleware name.
     *
     * @throws MiddlewareContractException if $name doesn't represent a class implementing the MiddlewareContract
     */
    public function withAddedMiddlewareName(string $name): MiddlewareNamesContract;

    /**
     * Returns a boolean indicating whether the instance has any MiddlewareContract.
     */
    public function hasAny(): bool;

    /**
     * Returns an array containing the collection of MiddlewareContract names.
     */
    public function toArray(): array;
}
