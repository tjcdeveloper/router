<?php
declare(strict_types=1);

namespace TJCDev\Router;

use InvalidArgumentException;
use JetBrains\PhpStorm\Pure;
use ReflectionClass;
use ReflectionException;
use TJCDev\Router\Contracts\MiddlewareInterface;
use TJCDev\Router\Contracts\RequestInterface;
use TJCDev\Router\Contracts\RouteInterface;
use TJCDev\Router\Contracts\RouterInterface;
use TJCDev\Router\Exceptions\InvalidHTTPMethodException;
use TJCDev\Router\Exceptions\MiddlewareException;
use TJCDev\Router\Exceptions\RouteNotFoundException;

class Router implements RouterInterface
{
    protected array $middleware = [];

    /**
     * @var Route[]
     */
    protected array $routes = [];

    /**
     * @return Route[]
     */
    public function getRoutes(): array
    {
        return $this->routes;
    }

    /**
     * @param string           $pattern
     * @param array|string     $methods
     * @param string|callable  $callback
     *
     * @return $this
     * @throws InvalidHTTPMethodException
     */
    public function make(string $pattern, array|string $methods, callable|string $callback): RouterInterface
    {
        $this->routes[] = new Route($pattern, $methods, $callback);
        return $this;
    }

    /**
     * @param string           $pattern
     * @param string|callable  $callback
     *
     * @return $this
     * @throws InvalidHTTPMethodException
     */
    public function makeDelete(string $pattern, callable|string $callback): RouterInterface
    {
        return $this->make($pattern, ['DELETE'], $callback);
    }

    /**
     * @param string           $pattern
     * @param string|callable  $callback
     *
     * @return $this
     * @throws InvalidHTTPMethodException
     */
    public function makeGet(string $pattern, callable|string $callback): RouterInterface
    {
        return $this->make($pattern, ['GET'], $callback);
    }

    /**
     * @param string           $pattern
     * @param string|callable  $callback
     *
     * @return $this
     * @throws InvalidHTTPMethodException
     */
    public function makePost(string $pattern, callable|string $callback): RouterInterface
    {
        return $this->make($pattern, ['POST'], $callback);
    }

    /**
     * @param string           $pattern
     * @param string|callable  $callback
     *
     * @return $this
     * @throws InvalidHTTPMethodException
     */
    public function makePut(string $pattern, callable|string $callback): RouterInterface
    {
        return $this->make($pattern, ['PUT'], $callback);
    }

    /**
     * @inheritDoc
     */
    #[Pure]
    public function matchRoute(RequestInterface $request): ?RouteInterface
    {
        foreach ($this->routes as $route) {
            if ($route->checkForMatch($request) !== false) {
                return $route;
            }
        }

        return null;
    }

    /**
     * @inheritDoc
     * @throws MiddlewareException
     * @throws RouteNotFoundException
     */
    public function middleware(string $alias): RouterInterface
    {
        if (is_null($route = $this->getLastRoute()))
            throw new RouteNotFoundException("There is no route available to assign this middleware to. You must make a route before attempting to assign any middleware.", 500);

        if (! array_key_exists($alias, $this->middleware))
            throw new MiddlewareException(sprintf('The middleware "%s" has not been registered. Use Router::registerMiddleware before assigning middleware to a route.', $alias), 500);

        $route->addMiddleware($this->middleware[$alias]);
    }

    /**
     * @inheritDoc
     * @throws InvalidArgumentException
     * @throws MiddlewareException
     */
    public function registerMiddleware(array|string $alias, ?string $className = null): RouterInterface
    {
        if (! is_array($alias)) {
            if (is_null($className))
                throw new InvalidArgumentException('ClassName cannot be null when a string is provided for alias.',
                    500);

            $alias = [$alias => $className];
        }

        foreach ($alias as $_alias => $_class) {
            if (array_key_exists($_alias, $this->middleware))
                throw new MiddlewareException(sprintf('The alias "%s" has already been assigned. Cannot reassign existing alias', $_alias), 500);

            try {
                $reflection = new ReflectionClass($_class);
                if (! $reflection->implementsInterface(MiddlewareInterface::class))
                    throw new InvalidArgumentException(sprintf('Expected middleware "%s" to implement MiddlewareInterface.', $_class), 500);

            } catch (ReflectionException $e) {
                throw new MiddlewareException(sprintf('An error occurred whilst assigning the middleware "%s": %s',
                    $_alias, $e->getMessage()), $e->getCode());
            }

            $this->middleware[$_alias] = $_class;
        }

        return $this;
    }

    #[Pure]
    protected function getLastRoute(): ?RouteInterface
    {
        return count($this->routes) ? $this->routes[array_key_last($this->routes)] : null;
    }

    /**
     * @inheritDoc
     */
    #[Pure]
    public function getMiddleware(string $alias): ?string
    {
        return array_key_exists($alias, $this->middleware) ? $this->middleware[$alias] : null;
    }
}
