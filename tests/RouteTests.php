<?php
declare(strict_types = 1);

namespace TJCDev\Router\Tests;

use PHPUnit\Framework\TestCase;
use TJCDev\Router\Exceptions\InvalidHTTPMethodException;
use TJCDev\Router\Route;

class RouteTests extends TestCase
{
    protected Route $specificUserRoute;
    protected Route $userRoute;
    
    /**
     * RouteTests constructor.
     *
     * @param  string|null  $name
     * @param  array        $data
     * @param  string       $dataName
     *
     * @throws InvalidHTTPMethodException
     */
    public function __construct(?string $name = null, array $data = [], $dataName = '')
    {
        $this->userRoute         = new Route("/users", ["GET", "POST"], fn() => "Users");
        $this->specificUserRoute = new Route("/users/{id}<\d+>", ["GET", "PUT", "DELETE"], fn() => "User!");
        parent::__construct($name, $data, $dataName);
    }
    
    public function testCheckMatchValidRoutes(): void
    {
        $this->assertTrue($this->userRoute->checkForMatch(["users"], "GET"));
        $this->assertTrue($this->userRoute->checkForMatch(["users"], "POST"));
        $this->assertTrue($this->specificUserRoute->checkForMatch(["users", "1"], "GET"));
        $this->assertTrue($this->specificUserRoute->checkForMatch(["users", "1"], "PUT"));
        $this->assertTrue($this->specificUserRoute->checkForMatch(["users", "123456"], "DELETE"));
    }
    
    public function testCheckNotMatchInvalidRoutes(): void
    {
        $this->assertNotTrue($this->userRoute->checkForMatch(["users", "invalid"], "GET"));
        $this->assertNotTrue($this->userRoute->checkForMatch(["users"], "DELETE"));
        $this->assertNotTrue($this->specificUserRoute->checkForMatch(["users"], "GET"));
        $this->assertNotTrue($this->specificUserRoute->checkForMatch(["users", "123"], "POST"));
        $this->assertNotTrue($this->specificUserRoute->checkForMatch(["users", "a-string"], "GET"));
    }
    
    public function testBreakPatternDown(): void
    {
        $expected = [
            [
                "key"     => "",
                "pattern" => "part-one",
                "regex"   => false
            ],
            [
                "key"     => "id",
                "pattern" => "/\d+/",
                "regex"   => true
            ],
            [
                "key"     => "",
                "pattern" => "part-three",
                "regex"   => false
            ]
        ];
        $route = new Route("/part-one/{id}<\d+>/part-three", "GET", fn() => "Test");
        $this->assertEquals($expected, $route->getSegments());
    }
}
