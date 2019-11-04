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

namespace Chevere\Components\App;

use Chevere\Components\App\Exceptions\RouterContractRequiredException;
use Chevere\Components\Message\Message;
use Chevere\Components\Router\Exception\RouteNotFoundException;
use Chevere\Contracts\App\BuilderContract;
use Chevere\Contracts\Router\RouterContract;

use function GuzzleHttp\Psr7\stream_for;

use const Chevere\CLI;

/**
 * Application resolver
 */
final class Resolver
{
    /** @var BuilderContract */
    private $builder;

    public function __construct(BuilderContract $builder)
    {
        $this->builder = $builder;
        $this->assertBuilderAppServicesRouter();
        $this->resolveController();
    }

    public function builder(): BuilderContract
    {
        return $this->builder;
    }

    private function assertBuilderAppServicesRouter(): void
    {
        if (!$this->builder->build()->app()->services()->hasRouter()) {
            throw new RouterContractRequiredException(
                (new Message('Instance of class %className% must contain a %contract% contract'))
                    ->code('%className%', get_class($this->builder->build()->app()))
                    ->code('%contract%', RouterContract::class)
                    ->toString()
            );
        }
    }

    private function resolveController(): void
    {
        $pathInfo = $this->builder->build()->app()->request()->getUri()->getPath();
        $app = $this->builder->build()->app();
        $route = $app->services()->router()->resolve($pathInfo);
        $app = $app
            ->withRoute($route);
        $this->builder = $this->builder
            ->withControllerName(
                $app->route()
                    ->getController($app->request()->getMethod())
            )
            ->withControllerArguments(
                $app->services()->router()->arguments()
            )
            ->withBuild(
                $this->builder->build()
                    ->withApp($app)
            );
    }
}
