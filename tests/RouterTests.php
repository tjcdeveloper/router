<?php
declare(strict_types = 1);

namespace TJCDev\Router\Tests;

use JetBrains\PhpStorm\Pure;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\UriInterface;
use TJCDev\Router\Exceptions\RouteNotFoundException;
use TJCDev\Router\Route;
use TJCDev\Router\Router;

class RouterTests extends TestCase
{
    use MakeRequestTrait;

    protected Router $router;
    protected string $testRouteResponse = 'Test route';

    public function __construct(?string $name = null, array $data = [], $dataName = '')
    {
        $this->router = new Router;
        parent::__construct($name, $data, $dataName);
    }

    #[Pure]
    protected function getLastMadeRoute(): Route
    {
        return $this->router->allRoutes()[array_key_last($this->router->allRoutes())];
    }

    public function testMakeGetRoute(): void
    {
        $this->router->make('/test-route', 'GET', fn() => $this->testRouteResponse);
        $route = $this->getLastMadeRoute();
        $this->assertEquals('/test-route', $route->getPattern());
        $this->assertEquals(['GET'], $route->getMethods());
    }

    public function testMakeRouteWithGet(): void
    {
        $this->router->makeGet('/test-route-2', fn() => 'Test route');
        $route = $this->getLastMadeRoute();
        $this->assertEquals('/test-route-2', $route->getPattern());
        $this->assertEquals(['GET'], $route->getMethods());
    }

    public function testMakeRouteWithPost(): void
    {
        $this->router->makePost('/test-route-post', fn() => 'Post route');
        $route = $this->getLastMadeRoute();
        $this->assertEquals('/test-route-post', $route->getPattern());
        $this->assertEquals(['POST'], $route->getMethods());
    }

    public function testMakeRouteWithPut(): void
    {
        $this->router->makePut('/test-route-put', fn() => 'Put route');
        $route = $this->getLastMadeRoute();
        $this->assertEquals('/test-route-put', $route->getPattern());
        $this->assertEquals(['PUT'], $route->getMethods());
    }

    public function testMakeRouteWithDelete(): void
    {
        $this->router->makeDelete('/test-route/{id}<\d+>', fn() => 'Delete route');
        $route = $this->getLastMadeRoute();
        $this->assertEquals('/test-route/{id}<\d+>', $route->getPattern());
        $this->assertEquals(['DELETE'], $route->getMethods());
    }

    /**
     * @depends testMakeGetRoute
     */
    public function testDispatchValidRoute(): void
    {
        $this->assertEquals($this->testRouteResponse, $this->router->dispatch($this->makeRequestStub('/test-route',
            'GET')));
    }

    public function testRouteNotFound(): void
    {
        $this->expectException(RouteNotFoundException::class);
        $this->router->dispatch($this->makeRequestStub('/invalid-route', 'GET'));
    }
}
