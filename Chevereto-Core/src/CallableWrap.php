<?php

declare(strict_types=1);

/*
 * This file is part of Chevereto\Core.
 *
 * (c) Rodolfo Berrios <rodolfo@chevereto.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Chevereto\Core;

use LogicException;
use ReflectionMethod;
use ReflectionFunction;
use ReflectionParameter;

/**
 * CallableWrap provides a object oriented way to interact with Chevereto\Core accepted callable strings.
 *
 * Accepted callable strings are:
 *
 * - A callable (function, method name)
 * - A class implementing ::__invoke
 * - A fileHandle string representing the path of a file wich returns a callable
 */
class CallableWrap extends Container
{
    // const SOURCE_FUNCTION = 'function';
    // const SOURCE_METHOD = 'method';
    // const SOURCE_CLASS = 'class';
    // const SOURCE_FILEHANDLE = 'fileHandle';

    const TYPE_FUNCTION = 'function';
    const TYPE_METHOD = 'method';
    const TYPE_CLASS = 'class';

    // const SOURCES = [self::SOURCE_FUNCTION, self::SOURCE_METHOD, self::SOURCE_CLASS, self::SOURCE_FILEHANDLE];
    const TYPES = [self::TYPE_FUNCTION, self::TYPE_CLASS];

    // TODO: Name "containerProperties"
    protected $objects = ['callableHandle', 'callable', 'callableFilepath', 'type', 'class', 'method', 'reflectionFunction', 'reflectionMethod', 'arguments', 'passedArguments'];

    /** @var string The callable string handle used to construct the object */
    // is_array (function)
    // Chevereto\Core\Path::fromHandle (method)
    // Chevereto\Core\Controllers\ApiGet (class implementing invoke)
    // callables:index (fileHandle return callable)
    protected $callableHandle;

    /** @var array explode('::', $callableHandle) */
    protected $callableHandleMethodExplode;

    /** @var callable The actual callable */
    protected $callable;

    /** @var string The callable file (if any) */
    protected $callableFilepath;

    /** @var string The callable type (function, method, class) */
    protected $type;

    /** @var string Class name (if any) */
    protected $class;

    /** @var string Method name (if any) */
    protected $method;

    /** @var ReflectionFunction The reflected callable (function) */
    protected $reflectionFunction;

    /** @var ReflectionMethod The reflected callable (method) */
    protected $reflectionMethod;

    /** @var string[] Callable parameters */
    protected $parameters;

    /** @var array Callable arguments */
    protected $arguments;

    /** @var array Passed callable arguments */
    protected $passedArguments;

    /** @var bool True if the callable comes from a fileHandle */
    protected $isFileHandle;

    /** @var bool True if the callable represents a anon function or class */
    protected $isAnon;

    public function __construct(string $callableHandle)
    {
        $this
            ->setIsFileHandle(false)
            ->setCallableHandle($callableHandle);
        // Direct processing for callable strings and invocable classes
        if (is_callable($callableHandle)) {
            $this
                ->setCallable($callableHandle)
                ->setIsAnon(false)
                ->prepare();
        } else {
            if (class_exists($callableHandle)) {
                if (method_exists($callableHandle, '__invoke')) {
                    $this
                        ->setCallable(new $callableHandle())
                        ->setClass($callableHandle)
                        ->setMethod('__invoke')
                        ->setIsAnon(false)
                        ->prepare();
                } else {
                    throw new LogicException(
                        (string)
                            (new Message('Missing magic method %s in class %c.'))
                                ->code('%s', '__invoke')
                                ->code('%c', $callableHandle)
                    );
                }
            }
        }
        // Some work needed when dealing with fileHandle
        if (!$this->hasObject('callable')) {
            if (Utils\Str::contains('::', $callableHandle)) {
                $this->callableHandleMethodExplode = explode('::', $callableHandle);
                $class = $this->callableHandleMethodExplode[0];
                if (!class_exists($class)) {
                    throw new LogicException(
                        (string)
                            (new Message('Callable string %s is targeting not found class %c.'))
                                ->code('%s', $callableHandle)
                                ->code('%c', $class)
                    );
                }
                $method = $this->callableHandleMethodExplode[1];
                if (0 === strpos($method, '__')) {
                    throw new LogicException(
                        (string)
                            (new Message('Callable string %s is targeting the magic method %m.'))
                                ->code('%s', $callableHandle)
                                ->code('%m', $method)
                    );
                }
                if (!method_exists($class, $method)) {
                    throw new LogicException(
                        (string)
                            (new Message('Callable string %s is targeting an nonexistent method %m.'))
                                ->code('%s', $callableHandle)
                                ->code('%m', $method)
                    );
                }
            } else {
                $callableFilepath = Path::fromHandle($callableHandle);
                if (!File::exists($callableFilepath)) {
                    throw new LogicException(
                        (string)
                            (new Message('Unable to locate any callable specified by %s.'))
                                ->code('%s', $callableHandle)
                    );
                }
                $callable = include $callableFilepath;
                if (!is_callable($callable)) {
                    throw new LogicException(
                        (string)
                            (new Message('Expected %s callable, %t provided in %f.'))
                                ->code('%s', '$callable')
                                ->code('%t', gettype($callable))
                                ->code('%f', $callableHandle)
                    );
                }
                $this
                    ->setIsFileHandle(true)
                    ->setCallable($callable)
                    ->setCallableFilepath($callableFilepath)
                    ->prepare();
            }
        }
    }

    protected function setCallableHandle(string $callableHandle): self
    {
        $this->callableHandle = $callableHandle;

        return $this;
    }

    public function getCallableHandle(): string
    {
        return $this->callableHandle;
    }

    protected function setCallable(callable $callable): self
    {
        $this->callable = $callable;

        return $this;
    }

    public function getCallable(): callable
    {
        return $this->callable;
    }

    protected function setCallableFilepath(string $filepath): self
    {
        $this->callableFilepath = $filepath;

        return $this;
    }

    public function getCallableFilepath(): ?string
    {
        return $this->callableFilepath;
    }

    protected function setType(string $type): self
    {
        if (!in_array($type, static::TYPES)) {
            throw new LogicException(
                (string)
                    (new Message('Invalid type %s, expecting one of these: %v.'))
                        ->code('%s', $type)
                        ->code('%v', implode(', ', static::TYPES))
            );
        }
        $this->type = $type;

        return $this;
    }

    public function getType(): string
    {
        return $this->type;
    }

    protected function setClass(string $class): self
    {
        $this->class = $class;

        return $this;
    }

    public function getClass(): string
    {
        return $this->class;
    }

    protected function setMethod(string $method): self
    {
        $this->method = $method;

        return $this;
    }

    public function getMethod(): string
    {
        return $this->method;
    }

    protected function setIsFileHandle(bool $isFileHandle): self
    {
        $this->isFileHandle = $isFileHandle;

        return $this;
    }

    public function isFileHandle(): bool
    {
        return $this->isFileHandle;
    }

    protected function setIsAnon(bool $isAnon): self
    {
        $this->isAnon = $isAnon;

        return $this;
    }

    public function isAnon(): bool
    {
        return $this->isAnon;
    }

    // Process the callable and fill the object properties
    protected function prepare()
    {
        if ($this->hasObject(static::TYPE_CLASS)) {
            $this->setType($this->hasObject(static::TYPE_METHOD) ? static::TYPE_CLASS : static::TYPE_FUNCTION);
        } else {
            if (is_object($this->getCallable())) {
                $this->setMethod('__invoke');
                $reflection = new \ReflectionClass($this->getCallable());
                $this->setType(static::TYPE_CLASS);
                $this->setIsAnon($reflection->isAnonymous());
                $this->setClass($this->isAnon() ? 'class@anonymous' : $reflection->getName());
            } else {
                $this->setType(static::TYPE_FUNCTION);
                if (isset($this->callableHandleMethodExplode)) {
                    $this
                        ->setClass($this->callableHandleMethodExplode[0])
                        ->setMethod($this->callableHandleMethodExplode[1]);
                }
            }
        }
    }

    protected function setParameters(array $parameters): self
    {
        $this->parameters = $parameters;

        return $this;
    }

    /**
     * Undocumented function.
     *
     * @return ReflectionParameter[]
     */
    public function getParameters(): ?array
    {
        if (!isset($this->parameters)) {
            $this->processParameters();
        }

        return $this->parameters;
    }

    protected function getReflectionFunction(): ReflectionFunction
    {
        return $this->reflectionFunction;
    }

    protected function getReflectionMethod(): ReflectionMethod
    {
        return $this->reflectionMethod;
    }

    protected function hasReflection(): bool
    {
        return $this->hasObject('reflectionFunction') || $this->hasObject('reflectionMethod');
    }

    protected function getReflection(): \ReflectionFunctionAbstract
    {
        return $this->hasObject('reflectionFunction')
            ? $this->getReflectionFunction()
            : $this->getReflectionMethod();
    }

    protected function processReflection(): self
    {
        if (is_object($this->getCallable())) {
            $this->reflectionMethod = new ReflectionMethod($this->getCallable(), $this->getMethod());
        } else {
            $this->reflectionFunction = new ReflectionFunction($this->getCallable());
        }

        return $this;
    }

    protected function processParameters(): self
    {
        if (!$this->hasReflection()) {
            $this->processReflection();
        }
        $reflection = $this->getReflection();

        $this->setParameters($reflection->getParameters());

        return $this;
    }

    protected function processArguments(): self
    {
        if (!$this->hasReflection()) {
            $this->processReflection();
        }
        $arguments = [];
        $parameterIndex = 0;
        // Magically create typehinted arguments
        foreach ($this->getReflection()->getParameters() as $parameter) {
            $parameterType = $parameter->getType();
            $type = null != $parameterType ? $parameterType->getName() : null;
            $value = $this->getPassedArguments()[$parameter->getName()] ?? $this->getPassedArguments()[$parameterIndex] ?? null;
            if (null === $type || in_array($type, Controller::TYPE_DECLARATIONS)) {
                $arguments[] = $value ?? ($parameter->isDefaultValueAvailable() ? $parameter->getDefaultValue() : null);
            } else {
                // Object typehint
                if (null === $value && $parameter->allowsNull()) {
                    $arguments[] = null;
                } else {
                    // $hasConstruct = method_exists($type, '__construct');
                    // if (!$hasConstruct) {
                    //     throw new LogicException(
                    //         (string)
                    //             (new Message("Class %s doesn't have a constructor. %n %o typehinted in %f invoke function."))
                    //                 ->code('%s', $type)
                    //                 ->code('%o', $type.' $'.$parameter->getName().($parameter->isDefaultValueAvailable() ? ' = '.$parameter->getDefaultValue() : null))
                    //                 ->code('%n', '#'.$parameter->getPosition())
                    //                 ->code('%f', $controller)
                    //     );
                    // }
                    $arguments[] = new $type($value);
                }
            }
            ++$parameterIndex;
        }
        $this->setArguments($arguments);

        return $this;
    }

    /**
     * Pass arguments to the callable which will be typehinted by this class.
     *
     * @param array $passedArguments
     *
     * @return self
     */
    public function setPassedArguments(array $passedArguments): self
    {
        $this->passedArguments = $passedArguments;

        return $this;
    }

    public function getPassedArguments(): array
    {
        return $this->passedArguments;
    }

    /**
     * Set the callable arguments.
     *
     * @param array $arguments callable arguments
     *
     * @return self
     */
    protected function setArguments(array $arguments): self
    {
        $this->arguments = $arguments;

        return $this;
    }

    public function getArguments(): array
    {
        if (!isset($this->arguments)) {
            $this->processArguments();
        }

        return $this->arguments;
    }
}
