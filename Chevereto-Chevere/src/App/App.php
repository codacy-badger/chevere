<?php

declare(strict_types=1);

/*
 * This file is part of Chevere.
 *
 * (c) Rodolfo Berrios <rodolfo@chevereto.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Chevere\App;

use LogicException;
use RuntimeException;
use const Chevere\ROOT_PATH;
use const Chevere\App\PATH as AppPath;
use Monolog\Logger;
use Chevere\Runtime\Runtime;
use Chevere\Router\Router;
use Chevere\HttpFoundation\Request;
use Chevere\HttpFoundation\Response;
use Chevere\Api\Api;
use Chevere\Api\Maker as ApiMaker;
use Chevere\App\src\Checkout;
use Chevere\File;
use Chevere\Path;
use Chevere\Interfaces\ControllerInterface;
use Chevere\Load;
use Chevere\Route\Route;
use Chevere\Route\ArrayFileWrap as RouteArrayFileWrap;
use Chevere\Console\Console;
use Chevere\Controller\ArgumentsWrap as ControllerArgumentsWrap;
use Chevere\Message;
use Chevere\Interfaces\AppInterface;
use Chevere\Interfaces\RenderableInterface;
use Chevere\Traits\StaticTrait;

/**
 * App contains the whole thing.
 */
final class App implements AppInterface
{
    use StaticTrait;

    const BUILD_FILEPATH = ROOT_PATH.AppPath.'build';
    const NAMESPACES = ['App', 'Chevere'];
    const APP = 'app';
    const FILEHANDLE_CONFIG = ':config';
    const FILEHANDLE_PARAMETERS = ':parameters';
    const FILEHANDLE_HACKS = ':hacks';

    /** @var bool */
    private $isCached;

    /** @var array|null An array containing string arguments (from request uri, cli) */
    private $arguments;

    /** @var array|null An array containing the prepared controller arguments (object injection) */
    private $controllerArguments;

    /** @var Runtime */
    private $runtime;

    /** @var Logger */
    // private $logger;

    /** @var Router */
    private $router;

    /** @var Request */
    private $request;

    /** @var Response */
    private $response;

    /** @var ApiMaker */
    private $api;

    /** @var Route */
    private $route;

    /** @var string */
    // private $cache;

    /** @var string */
    private $db;

    /** @var string */
    private $callable;

    /** @var App */
    private static $instance;

    /** @var Runtime */
    private static $defaultRuntime;

    /** @var bool True if run() has been called */
    private $ran;

    /*
    * (A) Router cache : The array which is used to resolve /req -> route (routing)
    * (B) Routes cache : The array of serialized Routes ['id' => Route serialized]
    * (C) Apis cache : The array containing the exposed API
    * ...
    * CHECK IF APP IS CACHED UNDER THE PROVIDED APIS+ROUTES
    * ...
    * new App:
    * 1. setParams (Runtime, [apis], [routes])
    * 2. isCached
    *      ? Router && API from Cache
    *      : Router && API on-the-fly
    * 3. Resolve controller
    *      - Router -> maps route id -> get Route -> return callable
    *
    * - Provide route access with some helper like Route::get('homepage@routes:web') which gets name=homepage from routes/web.php
    * - app/console dump:routes route:web -> Shows the return (generated objects) of this file
    * - App autoinjects a "Router", which could be Router::fromCache(...) or new Router() and provides access to Routes (cached or new)
    * - RouteCollection contains the array of mapped routes (objects or serialized arrays (cached))
    */
    public function __construct(Parameters $parameters = null)
    {
        self::setStaticInstance($this);
        $this->router = new Router();
        $this->isCached = false;
        if (self::hasStaticProp('defaultRuntime')) {
            $this->runtime = self::getDefaultRuntime();
        }
        if (false === stream_resolve_include_path(self::BUILD_FILEPATH)) {
            new Checkout(self::BUILD_FILEPATH);
        }
        Load::php(self::FILEHANDLE_HACKS);
        if (!isset($parameters)) {
            $pathHandle = Path::handle(self::FILEHANDLE_PARAMETERS);
            $parameters = Parameters::createFromFile($pathHandle);
        }
        // $this->processConfigFiles($parameters->getDataKey(Parameters::CONFIG_FILES));
        $this->processApi($parameters->getDataKey(Parameters::API));
        $this->processParamRoutes($parameters->getDatakey(Parameters::ROUTES));
        $this->response = new Response();
        $this->processSapi();
    }

    public function getBuildTime(): ?string
    {
        return File::exists(self::BUILD_FILEPATH) ? (string) file_get_contents(self::BUILD_FILEPATH) : null;
    }

    public function setCallable(string $callable): self
    {
        $this->callable = $callable;

        return $this;
    }

    /**
     * Run the callable and dispatch the handler.
     *
     * @param string $callable controller, needed when doing console command or testing
     */
    public function run()
    {
        if (isset($this->ran)) {
            throw new LogicException(
                (new Message('The method %s has been already called. The App can only run once.'))
                    ->code('%s', __METHOD__)
                    ->toString()
            );
        }
        $this->ran = true;
        if (!isset($this->callable)) {
            $this->processResolveCallable($this->request->getPathInfo());
        }
        if (isset($this->callable)) {
            $this->processController($this->callable);
        }
    }

    /**
     * Runs a explicit provided callable string.
     *
     * @param string $controller controller name
     */
    public function getControllerObject(string $controller)
    {
        // FIXME: Unified validation (Controller validator)
        if (!is_subclass_of($controller, ControllerInterface::class)) {
            throw new LogicException(
                (new Message('Callable %s must represent a class implementing the %i interface.'))
                    ->code('%s', $controller)
                    ->code('%i', ControllerInterface::class)
                    ->toString()
            );
        }
        $controller = new $controller($this);

        // if ($this->route instanceof Route) {
        //     $middlewares = $this->route->middlewares;
        //     if (!empty($middlewares)) {
        //         $handler = new Handler($middlewares);
        //         $handler->runner($this);
        //     }
        // }

        if (!empty($this->arguments)) {
            $wrap = new ControllerArgumentsWrap($controller, $this->arguments);
            $this->controllerArguments = $wrap->getArguments();
        } else {
            $this->controllerArguments = [];
        }

        $controller(...$this->controllerArguments);

        return $controller;
    }

    /**
     * Farewell kids, my planet needs me.
     */
    public function terminate(string $message = null)
    {
        if ($message) {
            Console::log($message);
        }
        // callTermEvent();
    }

    /**
     * Forges anRequest, wrapper for Symfony Request::create().
     *
     * @param string               $uri        The URI
     * @param string               $method     The HTTP method
     * @param array                $parameters The query (GET) or request (POST) parameters
     * @param array                $cookies    The request cookies ($_COOKIE)
     * @param array                $files      The request files ($_FILES)
     * @param array                $server     The server parameters ($_SERVER)
     * @param string|resource|null $content    The raw body data
     */
    public function forgeHttpRequest(...$requestArguments): self
    {
        if (isset($this->request)) {
            throw new LogicException('Unable to forge request when the request has been already set.');
        }
        if (!in_array($requestArguments[1], Route::HTTP_METHODS)) {
            throw new LogicException(
                (new Message('Unknown HTTP request method %s'))
                    ->code('%s', $requestArguments[1])
                    ->toString()
            );
        }
        $this->setRequest(Request::create(...$requestArguments));

        return $this;
    }

    // public function getHash(): string
    // {
    //     return ($this->getConstant('App\VERSION') ?: null).$this->getBuildTime();
    // }

    /**
     * @param array $arguments string arguments captured or injected
     */
    public function setArguments(array $arguments): AppInterface
    {
        $this->arguments = $arguments;

        return $this;
    }

    /**
     * @param array $arguments Prepared controller arguments
     */
    public function setControllerArguments(array $arguments)
    {
        $this->controllerArguments = $arguments;
    }

    public static function setDefaultRuntime(Runtime $runtime): void
    {
        self::$defaultRuntime = $runtime;
    }

    public static function getDefaultRuntime(): Runtime
    {
        return self::$defaultRuntime;
    }

    /**
     * Provides access to the App Request instance.
     */
    public static function requestInstance(): ?Request
    {
        // Request isn't there when doing cli (unless you run the request command)
        if (isset(self::$instance) && isset(self::$instance->request)) {
            return self::$instance->request;
        }

        return null;
    }

    /**
     * Provides access to the App Runtime instance.
     */
    public static function runtimeInstance(): Runtime
    {
        if (isset(self::$instance) && $runtimeInstance = self::$instance->runtime) {
            return $runtimeInstance;
        }
        throw new RuntimeException('NO RUNTIME INSTANCE EVERYTHING BURNS!');
    }

    public function route(): Route
    {
        return $this->route;
    }

    public function response(): Response
    {
        return $this->response;
    }

    // private function processConfigFiles(array $configFiles = null): void
    // {
    //     if (!isset($configFiles)) {
    //         return;
    //     }
    //     if (isset($this->runtime)) {
    //         $this->runtime->runConfig(
    //             (new Config())
    //                 ->processFromFiles($configFiles)
    //         );
    //     }
    // }

    private function setRequest(Request $request): self
    {
        $this->request = $request;

        $pathinfo = ltrim($this->request->getPathInfo(), '/');
        $this->request->attributes->set('requestArray', explode('/', $pathinfo));

        return $this;
    }

    private function processApi(string $pathIdentifier = null): void
    {
        if (!isset($pathIdentifier)) {
            return;
        }
        $api = new ApiMaker($this->router);
        if (!$this->isCached) {
            $api->register($pathIdentifier);
        }
        $this->api = $api;
        new Api($api);
    }

    private function processParamRoutes(array $paramRoutes = null): void
    {
        if (!isset($paramRoutes)) {
            return;
        }
        // ['handle' => [Routes,]]
        foreach ($paramRoutes as $fileHandleString) {
            $fileHandle = Path::handle($fileHandleString);
            foreach ((new RouteArrayFileWrap($fileHandle))->getArrayFile()->toArray() as $k => $route) {
                $this->router->addRoute($route, $fileHandleString);
            }
        }
    }

    private function processSapi(): void
    {
        if (Console::bind($this)) {
            Console::run(); // Note: Console::run() always exit.
        } else {
            $this->setRequest(Request::createFromGlobals());
        }
    }

    private function processController(string $controller): void
    {
        $controller = $this->getControllerObject($controller);
        if ($controller instanceof RenderableInterface) {
            echo $controller->render();
        } else {
            $this->response->send();
        }
    }

    private function processResolveCallable(string $pathInfo): void
    {
        // try {
        $this->route = $this->router->resolve($pathInfo);
        $this->callable = $this->route->getCallable($this->request->getMethod());
        $routerArgs = $this->router->arguments;
        // dd($routerArgs);
        if (isset($routerArgs)) {
            $this->setArguments($routerArgs);
        }
        // } catch (Throwable $e) {
        //     echo 'Exception at App: '.$e->getCode();

        //     return;
        // }
    }

    private static function setStaticInstance(App $app)
    {
        self::$instance = $app;
    }
}
