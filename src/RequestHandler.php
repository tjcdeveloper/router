<?php
declare(strict_types=1);

namespace TJCDev\Router;

use Exception;
use TJCDev\Router\Contracts\MiddlewareInterface;
use TJCDev\Router\Contracts\RequestHandlerInterface;
use TJCDev\Router\Contracts\RequestInterface;
use TJCDev\Router\Contracts\ResponseInterface;
use TJCDev\Router\Contracts\RouteInterface;
use TJCDev\Router\Contracts\RouterInterface;

class RequestHandler implements RequestHandlerInterface
{
    protected RequestInterface $request;
    protected RouterInterface $router;

    /**
     * @var callable[]
     */
    protected array $stack;

    public function handle(RequestInterface $request): ResponseInterface
    {
        try {
            $this->request = $request;
            $route = $this->router->matchRoute($request);
            if (is_null($route))
                return $this->generate404();

            $this->stack = $this->instantiateMiddleware($route);
            $this->stack[] = $route->getCallable();
            return $this->callNext();
        } catch (Exception $e) {
            return $this->generateError($e);
        }
    }

    public function registerRouter(RouterInterface $router): RequestHandler
    {
        $this->router = $router;
        return $this;
    }

    /**
     * @param RouteInterface  $route
     *
     * @return MiddlewareInterface[]
     */
    protected function instantiateMiddleware(RouteInterface $route): array
    {
        $instantiated = [];
        foreach ($route->getMiddleware() as $middleware) {
            $class = new ($this->router->getMiddleware($middleware));
            $instantiated[] = [$class, 'handle'];
        }
        return $instantiated;
    }

    protected function generate404(): ResponseInterface
    {

    }

    protected function generateError(Exception $e): ResponseInterface
    {

    }

    /**
     * @return ResponseInterface
     */
    public function callNext(): ResponseInterface
    {
        $next = array_pop($this->stack);
        return count($this->stack) ? $next($this->request, [$this, 'callNext']) : $this->callRouteCallback($next);
    }

    /**
     * Call the callback specified in the route definition. Check the response that comes back, and if it's not a
     * valid ResponseInterface object wrap it in a new Response using the return from the callback as the response body.
     *
     * @param callable  $controller
     *
     * @return Response
     */
    protected function callRouteCallback(callable $controller): Response
    {
        $response = $controller();
        if ( ! $response instanceof ResponseInterface) {
            $body = $response;
            $response = new Response();
            $response->setBody($body);
        }
        return $response;
    }
}
