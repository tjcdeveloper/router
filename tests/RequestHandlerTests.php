<?php
declare(strict_types=1);

namespace TJCDev\Router\Tests;

use Exception;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use TJCDev\Router\Contracts\ResponseInterface;
use TJCDev\Router\Contracts\RouterInterface;
use TJCDev\Router\RequestHandler;
use TJCDev\Router\Route;

class RequestHandlerTests extends TestCase
{
    use MockRouteTrait;

    protected string $responseBody = "Test response body";

    public function testGetResponse(): void
    {
        $handler = new RequestHandler();
        $response = $handler->registerRouter($this->createMockRouter(true, $this->createMockRoute()))
                            ->handle($this->createMockRequest('/my/path', 'GET'));
        $this->assertInstanceOf(ResponseInterface::class, $response, "RequestHandler::handle() should return an instance of ResponseInterface.");
        $reflection = new ReflectionClass($response);
        $body = $reflection->getProperty('body');
        $body->setAccessible(true);
        $this->assertEquals($this->responseBody, $body->getValue($response), "Response::body should contain the return from the Route callable when the callable does not return an instance of ResponseInterface.");
        $code = $reflection->getProperty('code');
        $code->setAccessible(true);
        $this->assertEquals(200, $code->getValue($response), "Response::code should be 200 for a successful request.");
    }

    public function testRouteNotFound(): void
    {
        $handler = new RequestHandler();
        $response = $handler->registerRouter($this->createMockRouter(false))
                            ->handle($this->createMockRequest('/fake/path', 'POST'));
        $this->assertInstanceOf(ResponseInterface::class, $response, "RequestHandler::handle() should return an instance of ResponseInterface, even for routes that produce errors.");
        $reflection = new ReflectionClass($response);
        $code = $reflection->getProperty('code');
        $code->setAccessible(true);
        $this->assertEquals(404, $code->getValue($response), "Response::code should be 404 for a route that does not exist.");
    }

    public function testErrorResponse(): void
    {
        $message = "Test exception";
        $handler = new RequestHandler();
        $route = $this->getMockBuilder(Route::class)
                      ->onlyMethods(['checkForMatch', 'getCallable', 'getMiddleware'])
                      ->disableOriginalConstructor()
                      ->getMock();
        $route->method('checkForMatch')->willReturn(true);
        $route->method('getCallable')->willThrowException(new Exception($message, 501));
        $route->method('getMiddleware')->willReturn([]);
        $router = $this->createMockRouter(true, $route);

        $response = $handler->registerRouter($router)
                            ->handle($this->createMockRequest('/some/path', 'POST'));
        $reflection = new ReflectionClass($response);

        $code = $reflection->getProperty('code');
        $code->setAccessible(true);
        $this->assertEquals(501, $code->getValue($response), "RequestHandler::handle() should return an object with a 501 response code when an exception is thrown with a code of 501.");

        $body = $reflection->getProperty('body');
        $body->setAccessible(true);
        $actualBody = $body->getValue($response);
        $this->assertArrayHasKey('data', $actualBody, "The body should contain an array with a single key, data");
        $this->assertEquals('ERROR', $actualBody['data']['status'], "The data array should contain a status with a value of ERROR.");
        $this->assertEquals(501, $actualBody['data']['code'], "The data array should contain a code with a value of 501");
        $this->assertEquals($message, $actualBody['data']['message'], "The data array should contain a message with a value of the exception message.");
    }

    protected function createMockRouter(bool $success = true, Route|MockObject $route = null):
    RouterInterface|MockObject
    {
        $router = $this->getMockBuilder(RouterInterface::class)
                       ->onlyMethods(['getRoutes', 'make', 'matchRoute', 'middleware', 'registerMiddleware', 'getMiddleware'])
                       ->getMock();
        $router->method('getRoutes')->willReturn([$route]);
        $router->method('make')->willReturnSelf();
        $router->method('matchRoute')->willReturn($success ? $route : null);
        $router->method('middleware')->willReturnSelf();
        $router->method('registerMiddleware')->willReturnSelf();
        return $router;
    }

    protected function createMockRoute(): Route|MockObject
    {
        $route = $this->getMockBuilder(Route::class)
                      ->onlyMethods(['checkForMatch', 'getCallable', 'getMiddleware'])
                      ->disableOriginalConstructor()
                      ->getMock();
        $route->method('checkForMatch')->willReturn(true);
        $route->method('getCallable')->willReturn(fn() => $this->responseBody);
        $route->method('getMiddleware')->willReturn([]);
        return $route;
    }
}
