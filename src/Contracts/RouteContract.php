<?php
declare(strict_types = 1);

namespace TJCDev\Router\Contracts;

use Closure;
use Psr\Http\Message\RequestInterface;
use TJCDev\Router\Exceptions\InvalidHTTPMethodException;

interface RouteContract
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
     * Check to see if the passed in exploded route matches this route.
     *
     * @param RequestInterface  $request
     *
     * @return array|bool Returns false or an array of arguments to be passed to the controller
     */
    public function checkForMatch(RequestInterface $request): array|bool;

    public function dispatch(): mixed;
}
