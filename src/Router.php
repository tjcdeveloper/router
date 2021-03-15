<?php
declare(strict_types = 1);

namespace TJCDev\Router;

use JetBrains\PhpStorm\Pure;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use TJCDev\Router\Exceptions\RouteNotFoundException;

class Router
{
    /**
     * @var Route[]
     */
    protected array $routes = [];

    /**
     * @return Route[]
     */
    public function allRoutes(): array
    {
        return $this->routes;
    }

    /**
     * @param  string           $pattern
     * @param  array|string     $methods
     * @param  string|callable  $callback
     *
     * @return $this
     * @throws Exceptions\InvalidHTTPMethodException
     */
    public function make(string $pattern, array|string $methods, Callable|string $callback): Router
    {
        $this->routes[] = new Route($pattern, $methods, $callback);
        return $this;
    }

    /**
     * @param  string           $pattern
     * @param  string|callable  $callback
     *
     * @return $this
     * @throws Exceptions\InvalidHTTPMethodException
     */
    public function makeDelete(string $pattern, Callable|string $callback): Router
    {
        return $this->make($pattern, ['DELETE'], $callback);
    }

    /**
     * @param  string           $pattern
     * @param  string|callable  $callback
     *
     * @return $this
     * @throws Exceptions\InvalidHTTPMethodException
     */
    public function makeGet(string $pattern, Callable|string $callback): Router
    {
        return $this->make($pattern, ['GET'], $callback);
    }

    /**
     * @param  string           $pattern
     * @param  string|callable  $callback
     *
     * @return $this
     * @throws Exceptions\InvalidHTTPMethodException
     */
    public function makePost(string $pattern, Callable|string $callback): Router
    {
        return $this->make($pattern, ['POST'], $callback);
    }

    /**
     * @param  string           $pattern
     * @param  string|callable  $callback
     *
     * @return $this
     * @throws Exceptions\InvalidHTTPMethodException
     */
    public function makePut(string $pattern, Callable|string $callback): Router
    {
        return $this->make($pattern, ['PUT'], $callback);
    }

    /**
     * @param RequestInterface  $request
     *
     * @return ResponseInterface
     * @throws RouteNotFoundException
     */
    public function dispatch(RequestInterface $request): ResponseInterface
    {
        if ( ! ($route = $this->matchRoute($request))) {
            throw new RouteNotFoundException();
        }

        return $route->dispatch();
    }

    /**
     * @param RequestInterface  $request
     *
     * @return Route|null
     */
    #[Pure]
    protected function matchRoute(RequestInterface $request): ?Route
    {
        foreach ($this->routes as $route) {
            if ($route->checkForMatch($request)) {
                return $route;
            }
        }

        return null;
    }
}
