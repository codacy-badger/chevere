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

namespace Chevere\Components\Router;

use Chevere\Components\Router\Properties\GroupsProperty;
use Chevere\Components\Router\Properties\IndexProperty;
use Chevere\Components\Router\Properties\NamedProperty;
use Chevere\Components\Router\Properties\RegexProperty;
use Chevere\Components\Router\Properties\RoutesProperty;
use Chevere\Contracts\Router\RouterPropertiesContract;

final class RouterProperties implements RouterPropertiesContract
{
    /** @var array RegexPropertyContract::class[] */
    private $classMap = [
        RegexProperty::class,
        RoutesProperty::class,
        IndexProperty::class,
        GroupsProperty::class,
        NamedProperty::class,
    ];

    /** @var string */
    private $regex;

    /** @var array */
    private $routes;

    /** @var array */
    private $index;

    /** @var array */
    private $groups;

    /** @var array */
    private $named;

    /**
     * {@inheritdoc}
     */
    public function __construct()
    {
        $this->regex = '';
        $this->routes = [];
        $this->index = [];
        $this->groups = [];
        $this->named = [];
    }

    /**
     * {@inheritdoc}
     */
    public function withRegex(string $regex): RouterPropertiesContract
    {
        $new = clone $this;
        $new->regex = $regex;

        return $new;
    }

    public function hasRegex(): bool
    {
        return '' != $this->regex;
    }

    /**
     * {@inheritdoc}
     */
    public function regex(): string
    {
        return $this->regex;
    }

    /**
     * {@inheritdoc}
     */
    public function withRoutes(array $routes): RouterPropertiesContract
    {
        $new = clone $this;
        $new->routes = $routes;

        return $new;
    }

    /**
     * {@inheritdoc}
     */
    public function routes(): array
    {
        return $this->routes;
    }

    /**
     * {@inheritdoc}
     */
    public function withIndex(array $index): RouterPropertiesContract
    {
        $new = clone $this;
        $new->index = $index;

        return $new;
    }

    /**
     * {@inheritdoc}
     */
    public function index(): array
    {
        return $this->index;
    }

    /**
     * {@inheritdoc}
     */
    public function withGroups(array $groups): RouterPropertiesContract
    {
        $new = clone $this;
        $new->groups = $groups;

        return $new;
    }

    /**
     * {@inheritdoc}
     */
    public function groups(): array
    {
        return $this->groups;
    }

    /**
     * {@inheritdoc}
     */
    public function withNamed(array $named): RouterPropertiesContract
    {
        $new = clone $this;
        $new->named = $named;

        return $new;
    }

    /**
     * {@inheritdoc}
     */
    public function named(): array
    {
        return $this->named;
    }

    /**
     * {@inheritdoc}
     */
    public function assert(): void
    {
        foreach ($this->classMap as $className) {
            $prop = $className::NAME;
            new $className($this->{$prop});
        }
    }

    /**
     * {@inheritdoc}
     */
    public function toArray(): array
    {
        $array = [];
        foreach ($this->classMap as $className) {
            $prop = $className::NAME;
            $array[$prop] = $this->{$prop};
        }

        return $array;
    }
}
