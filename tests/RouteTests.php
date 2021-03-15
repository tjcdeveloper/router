<?php
declare(strict_types=1);

namespace TJCDev\Router\Tests;

use http\Env\Response;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\UriInterface;
use ReflectionClass;
use stdClass;
use TJCDev\Router\Exceptions\InvalidHTTPMethodException;
use TJCDev\Router\Route;

class RouteTests extends TestCase
{
    protected Route $specificUserRoute;
    protected Route $userRoute;

    /**
     * RouteTests constructor.
     *
     * @param string|null  $name
     * @param array        $data
     * @param string       $dataName
     *
     * @throws InvalidHTTPMethodException
     */
    public function __construct(?string $name = null, array $data = [], $dataName = '')
    {
        $responseStub = $this->createStub(ResponseInterface::class);
        $responseStub->method('getBody')
            ->willReturn('Response body');
        $this->userRoute = new Route("/users", ["get", "PoST"], fn() => $responseStub);
        $this->specificUserRoute = new Route("/users/{id}<\d+>", ["GET", "PUT", "DELETE"], fn() => $responseStub);
        parent::__construct($name, $data, $dataName);
    }

    public function testCheckMatchValidPostRoute(): void
    {
        $expected = [];
        $uriStub = $this->createStub(UriInterface::class);
        $uriStub->method('getPath')
            ->willReturn('/users');
        $requestStub = $this->createStub(RequestInterface::class);
        $requestStub->method('getMethod')
            ->willReturn('POST');
        $requestStub->method('getUri')
            ->willReturn($uriStub);
        $this->assertEquals($expected, $this->userRoute->checkForMatch($requestStub), "checkForMatch should successfully match a valid route using the POST method.");
    }

    public function testCheckMatchValidGetRoute(): void
    {
        $expected = [];
        $uriStub = $this->createStub(UriInterface::class);
        $uriStub->method('getPath')
            ->willReturn('/users');
        $requestStub = $this->createStub(RequestInterface::class);
        $requestStub->method('getMethod')
            ->willReturn('GET');
        $requestStub->method('getUri')
            ->willReturn($uriStub);
        $this->assertEquals($expected, $this->userRoute->checkForMatch($requestStub), "checkForMatch should successfully match a valid route using the GET method.");
    }

    public function testCheckMatchValidRouteWithVariable(): void
    {
        $expected = ['id' => 123];
        $uriStub = $this->createStub(UriInterface::class);
        $uriStub->method('getPath')
            ->willReturn('/users/123');
        $requestStub = $this->createStub(RequestInterface::class);
        $requestStub->method('getMethod')
            ->willReturn('GET');
        $requestStub->method('getUri')
            ->willReturn($uriStub);
        $this->assertEquals($expected, $this->specificUserRoute->checkForMatch($requestStub), "checkForMatch should successfully match a valid route using the GET method and identify the ID in the path.");
    }

    public function testCheckMatchInvalidPath(): void
    {
        $uriStub = $this->createStub(UriInterface::class);
        $uriStub->method('getPath')
            ->willReturn('/users/invalid/path');
        $requestStub = $this->createStub(RequestInterface::class);
        $requestStub->method('getMethod')
            ->willReturn('GET');
        $requestStub->method('getUri')
            ->willReturn($uriStub);
        $this->assertFalse($this->userRoute->checkForMatch($requestStub), "checkForMatch should reject an invalid path.");
    }

    public function testCheckMatchInvalidMethod(): void
    {
        $uriStub = $this->createStub(UriInterface::class);
        $uriStub->method('getPath')
            ->willReturn('/users');
        $requestStub = $this->createStub(RequestInterface::class);
        $requestStub->method('getMethod')
            ->willReturn('DELETE');
        $requestStub->method('getUri')
            ->willReturn($uriStub);
        $this->assertFalse($this->userRoute->checkForMatch($requestStub), "checkForMatch should reject a valid route using an invalid method");
    }

    public function testCheckMatchMissingVariable(): void
    {
        $uriStub = $this->createStub(UriInterface::class);
        $uriStub->method('getPath')
            ->willReturn('/users');
        $requestStub = $this->createStub(RequestInterface::class);
        $requestStub->method('getMethod')
            ->willReturn('GET');
        $requestStub->method('getUri')
            ->willReturn($uriStub);
        $this->assertFalse($this->specificUserRoute->checkForMatch($requestStub), "checkForMatch should reject a route missing a required variable");
    }

    public function testCheckMatchInvalidVariable(): void
    {
        $uriStub = $this->createStub(UriInterface::class);
        $uriStub->method('getPath')
            ->willReturn('/users/a-string');
        $requestStub = $this->createStub(RequestInterface::class);
        $requestStub->method('getMethod')
            ->willReturn('GET');
        $requestStub->method('getUri')
            ->willReturn($uriStub);
        $this->assertFalse($this->specificUserRoute->checkForMatch($requestStub), "checkForMatch should reject a route with a variable that does not match the specified pattern");
    }

    public function testBreakPatternDown(): void
    {
        $expected = [
            [
                "key" => "",
                "pattern" => "part-one",
                "regex" => false,
            ],
            [
                "key" => "id",
                "pattern" => "/\d+/",
                "regex" => true,
            ],
            [
                "key" => "",
                "pattern" => "part-three",
                "regex" => false,
            ],
        ];
        $route = new Route("/part-one/{id}<\d+>/part-three", "GET", fn() => $this->createStub(ResponseInterface::class));
        $this->assertEquals($expected, $route->getSegments(), "A Route should break down the pattern into individual segments, highlighting variables for regex matching");
    }

    public function testRouteDispatchWithCallback(): void
    {
        $this->assertEquals("Response body", $this->userRoute->dispatch()->getBody(), "Route::dispatch should execute the callback and return the result");
    }

    public function testRouteDispatchWithClass(): void
    {
        $route = new Route("/test/route", "GET", TestController::class . "@index");
        $this->assertEquals('My response', $route->dispatch());
    }
}

class TestController
{
    public function index(): string
    {
        return 'My response';
    }
}
