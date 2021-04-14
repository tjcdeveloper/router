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
use TJCDev\Router\Exceptions\RouteNotFoundException;

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
                throw new RouteNotFoundException(sprintf('No matching route found for "%s"', $request->getPath()));

            $this->stack = $this->instantiateMiddleware($route);
            $this->stack[] = $route->getCallable();
            return $this->callNext();
        } catch (RouteNotFoundException $e) {
            return $this->generate404($e);
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

    protected function generate404(RouteNotFoundException $e): ResponseInterface
    {
        $response = new Response();
        $response->setCode(404)
                 ->setBody(['data' => ['status' => 'ERROR', 'code' => '404', 'message' => 'Page Not Found']]);

        return $response;
    }

    protected function generateError(Exception $e): ResponseInterface
    {
        $response = new Response();
        $response->setCode(in_array($e->getCode(), Response::VALID_RESPONSE_CODES) ? $e->getCode() : 500)
                 ->setBody(['data' => ['status' => 'ERROR', 'code' => $e->getCode(), 'message' => $e->getMessage()]]);
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
