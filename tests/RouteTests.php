<?php
declare(strict_types=1);

namespace TJCDev\Router\Tests;

use PHPUnit\Framework\TestCase;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\UriInterface;
use TJCDev\Router\Exceptions\InvalidHTTPMethodException;
use TJCDev\Router\Route;

class RouteTests extends TestCase
{
    use MakeRequestTrait;

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
        $this->userRoute = new Route("/users", ["get", "PoST"], fn() => 'Users');
        $this->specificUserRoute = new Route("/users/{id}<\d+>", ["GET", "PUT", "DELETE"], fn() => 'Specific user');
        parent::__construct($name, $data, $dataName);
    }

    public function testCheckMatchValidPostRoute(): void
    {
        $expected = [];
        $this->assertEquals($expected, $this->userRoute->checkForMatch($this->makeRequestStub('/users', 'POST')), "checkForMatch should successfully match a valid route using the POST method.");
    }

    public function testCheckMatchValidGetRoute(): void
    {
        $expected = [];
        $this->assertEquals($expected, $this->userRoute->checkForMatch($this->makeRequestStub('/users', 'GET')), "checkForMatch should successfully match a valid route using the GET method.");
    }

    public function testCheckMatchValidRouteWithVariable(): void
    {
        $expected = ['id' => 123];
        $this->assertEquals($expected, $this->specificUserRoute->checkForMatch($this->makeRequestStub('/users/123', 'GET')),
            "checkForMatch should successfully match a valid route using the GET method and identify the ID in the path.");
    }

    public function testCheckMatchInvalidPath(): void
    {
        $this->assertFalse($this->userRoute->checkForMatch($this->makeRequestStub('/users/invalid/path', 'GET')), "checkForMatch should reject an invalid path.");
    }

    public function testCheckMatchInvalidMethod(): void
    {
        $this->assertFalse($this->userRoute->checkForMatch($this->makeRequestStub('/users', 'DELETE')), "checkForMatch should reject a valid route using an invalid method");
    }

    public function testCheckMatchMissingVariable(): void
    {
        $this->assertFalse($this->specificUserRoute->checkForMatch($this->makeRequestStub('/users', 'GET')), "checkForMatch should reject a route missing a required variable");
    }

    public function testCheckMatchInvalidVariable(): void
    {
        $this->assertFalse($this->specificUserRoute->checkForMatch($this->makeRequestStub('/users/a-string', 'GET')),
            "checkForMatch should reject a route with a variable that does not match the specified pattern");
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
        $route = new Route("/part-one/{id}<\d+>/part-three", "GET", fn() => '3 part response');
        $this->assertEquals($expected, $route->getSegments(), "A Route should break down the pattern into individual segments, highlighting variables for regex matching");
    }

    public function testRouteDispatchWithCallback(): void
    {
        $this->assertEquals("Users", $this->userRoute->dispatch(), "Route::dispatch should execute the callback and return the result");
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
