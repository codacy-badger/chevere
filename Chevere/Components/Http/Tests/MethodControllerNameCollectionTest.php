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

namespace Chevere\Components\Http\Tests;

use Chevere\Components\Controller\ControllerName;
use Chevere\Components\Http\Exceptions\MethodNotFoundException;
use Chevere\Components\Http\Method;
use Chevere\Components\Http\MethodControllerName;
use Chevere\Components\Http\MethodControllerNameCollection;
use Chevere\Components\Http\Interfaces\MethodInterface;
use Chevere\Components\Http\Interfaces\MethodControllerNameInterface;
use Chevere\TestApp\App\Controllers\TestController;
use PHPUnit\Framework\TestCase;

final class MethodControllerNameCollectionTest extends TestCase
{
    private function getMethodControllerName(MethodInterface $method): MethodControllerNameInterface
    {
        return
            new MethodControllerName(
                $method,
                new ControllerName(TestController::class)
            );
    }

    public function testConstructEmpty(): void
    {
        $method = new Method('GET');
        $collection = new MethodControllerNameCollection();
        $this->assertFalse($collection->hasAny());
        $this->assertFalse(
            $collection->has($method)
        );
        $this->assertSame([], $collection->toArray());
        $this->expectException(MethodNotFoundException::class);
        $collection->get($method);
    }

    public function testwithAddedMethodControllerName(): void
    {
        $collection = new MethodControllerNameCollection();
        $aux = [];
        foreach (MethodInterface::ACCEPT_METHOD_NAMES as $methodName) {
            $method = new Method($methodName);
            $methodControllerName = $this->getMethodControllerName($method);
            $collection = $collection
                ->withAddedMethodControllerName($methodControllerName);
            $this->assertTrue($collection->has($method));
            $this->assertSame($methodControllerName, $collection->get($method));
            $aux[] = $methodControllerName;
        }
        $this->assertTrue($collection->hasAny());
        $this->assertSame($aux, $collection->toArray());
    }
}
