<?php
declare(strict_types = 1);

namespace TJCDev\Router\Contracts;

use Closure;
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
     * @param  array   $segments  A route separated into individual segments
     *                            E.g. explode('/', trim('/my/route', '/'));
     * @param  string  $method
     *
     * @return bool
     */
    public function checkForMatch(array $segments, string $method): bool;
}
