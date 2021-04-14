<?php
declare(strict_types = 1);

namespace TJCDev\Router\Contracts;

use Closure;
use TJCDev\Router\Exceptions\InvalidHTTPMethodException;

interface RouteInterface
{
    /**
     * RouteContract constructor.
     *
     * @param  string          $pattern
     * @param  array|string    $methods
     * @param  Closure|string  $callable  Closure or class and method to be invoked using the fully qualified class
     *                                    name. Example: 'App\Controller\MyController::myMethod'
     *
     * @throws InvalidHTTPMethodException
     */
    public function __construct(string $pattern, array|string $methods, Closure|string $callable);

    /**
     * Add middleware to be run when this route is dispatched.
     *
     * @param string|array  $middleware
     *
     * @return RouteInterface
     */
    public function addMiddleware(string|array $middleware): RouteInterface;

    /**
     * Check to see if the passed in exploded route matches this route.
     *
     * @param RequestInterface  $request
     *
     * @return array|bool Returns false or an array of arguments to be passed to the controller
     */
    public function checkForMatch(RequestInterface $request): array|bool;

    /**
     * Return the closure or instantiated class and method named in the $callable argument during construction.
     *
     * @return Callable
     */
    public function getCallable(): Callable;

    /**
     * Return the list of middleware that has been added to this route.
     *
     * @return string[]
     */
    public function getMiddleware(): array;
}
