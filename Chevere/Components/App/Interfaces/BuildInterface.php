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

namespace Chevere\Components\App\Interfaces;

use Chevere\Components\Filesystem\Dir\Interfaces\DirInterface;
use Chevere\Components\Filesystem\Interfaces\FileInterface;
use Chevere\Components\Router\Interfaces\RouterMakerInterface;

interface BuildInterface
{
    public function __construct(AppInterface $app);

    /**
     * Return an instance with the specified ServicesInterface.
     *
     * This method MUST retain the state of the current instance, and return
     * an instance that contains the specified ServicesInterface.
     */
    public function withApp(AppInterface $services): BuildInterface;

    /**
     * Provides access to the ServicesInterface instance.
     */
    public function app(): AppInterface;

    /**
     * Return an instance with the specified ParametersInterface.
     *
     * This method MUST retain the state of the current instance, and return
     * an instance that contains the specified Container.
     */
    public function withParameters(ParametersInterface $parameters): BuildInterface;

    /**
     * Returns a boolean indicating whether the instance has a ParametersInterface.
     */
    public function hasParameters(): bool;

    /**
     * Provides access to the ParametersInterface instance.
     */
    public function parameters(): ParametersInterface;

    /**
     * Handles the API and route parameters and makes the application build.
     * Note: Can be only called once.
     */
    public function withRouterMaker(RouterMakerInterface $roterMaker): BuildInterface;

    public function hasRouterMaker(): bool;

    public function routerMaker(): RouterMakerInterface;

    /**
     * Make the application build.
     */
    public function make(): BuildInterface;

    /**
     * Returns true if the build has been just maked.
     */
    public function isMaked(): bool;

    /**
     * Destroy the application build (file plus any application cache).
     */
    public function destroy(): void;

    /**
     * Provides access to the FileInterface contained in the FilePhpInterface instance.
     */
    public function file(): FileInterface;

    /**
     * Provides access to the DirInterface instance.
     */
    public function dir(): DirInterface;

    /**
     * Provides access to the build checksums.
     * Note: This method is available if the application build has been built.
     *
     * @see BuilderInterface::isMaked()
     */
    public function checksums(): array;

    /**
     * Provides access to the CheckoutInterface instance.
     * Note: This method is available if the application build has been built.
     *
     * @see BuilderInterface::isMaked()
     */
    public function checkout(): CheckoutInterface;
}
