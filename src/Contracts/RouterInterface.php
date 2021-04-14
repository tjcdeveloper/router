<?php
declare(strict_types=1);

namespace TJCDev\Router\Contracts;

use InvalidArgumentException;
use JetBrains\PhpStorm\Pure;
use TJCDev\Router\Exceptions\MiddlewareException;
use TJCDev\Router\Exceptions\RouteNotFoundException;

interface RouterInterface
{
    /**
     * Fetch all registered routes.
     *
     * @return RouteInterface[]
     */
    public function getRoutes(): array;

    /**
     * Create a new route within the Router.
     *
     * @param string           $pattern
     * @param array|string     $methods
     * @param callable|string  $callback
     *
     * @return RouterInterface
     */
    public function make(string $pattern, array|string $methods, callable|string $callback): RouterInterface;

    /**
     * Check to see if a request matches any registered route, and if so return that route. Otherwise returns null.
     *
     * @param RequestInterface  $request
     *
     * @return RouteInterface|null
     */
    #[Pure]
    public function matchRoute(RequestInterface $request): ?RouteInterface;

    /**
     * Add the registered middleware to the last created route, or all routes within the current group.
     *
     * @param string  $alias
     *
     * @return RouterInterface
     */
    public function middleware(string $alias): RouterInterface;

    /**
     * Register middleware with the router, ready to be used by routes.
     *
     * @param array|string  $alias  Alias to use for the middleware, or an array of Middleware to be registered
     *                              using [$alias => $className] format.
     * @param string|null   $className
     *
     * @return RouterInterface
     */
    public function registerMiddleware(array|string $alias, ?string $className = null): RouterInterface;

    /**
     * Convert an alias into matching registered middlewares FQN.
     *
     * @param string  $alias
     *
     * @return string|null
     */
    public function getMiddleware(string $alias): ?string;
}
