<?php
declare(strict_types=1);

namespace TJCDev\Router\Tests;

use PHPUnit\Framework\TestCase;
use ReflectionClass;
use TJCDev\Router\Request;

class RequestTests extends TestCase
{
    public function testCreateRequest(): void
    {
        $query = ['query' => 'true', 'string' => 'yes'];
        $post = ['foo' => 'bar'];
        $request = new Request("Get", "/some/path", $query, $post);
        $this->assertEquals("GET", $request->getMethod(), "Request should accept an HTTP method and convert it to uppercase.");
        $this->assertEquals("/some/path", $request->getPath(), "Request should accept a path.");
        $this->assertEquals($query, $request->getQuery(), "Request should accept an array representing the query string.");
        $this->assertEquals($post, $request->getRequest(), "Request should accept an array representing the body of a request.");
    }

    public function testCreateFromServer(): void
    {
        $_GET = ['query' => 'true', 'string' => 'yes'];
        $_POST = ['foo' => 'bar', 'baz' => 'buzz'];
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_SERVER['REQUEST_URI'] = '/some/path?query=true&string=yes';
        $request = Request::fromServer();
        $this->assertEquals('POST', $request->getMethod(), 'Request::fromServer() should get the method from 
        $_SERVER[\'REQUEST_METHOD\'].');
        $this->assertEquals('/some/path', $request->getPath(), 'Request::fromServer() should get the path from $_SERVER[\'REQUEST_URI\'] without the query string.');
        $this->assertEquals($_GET, $request->getQuery(), 'Request::fromServer() should get the query string from $_GET.');
        $this->assertEquals($_POST, $request->getRequest(), 'Request::fromServer() should get the request body from $_POST.');
    }

    public function testQueryString(): void
    {
        $request = new Request("GET", "/some/path");
        $reflection = new ReflectionClass($request);
        $method = $reflection->getMethod('stripQueryString');
        $method->setAccessible(true);
        $this->assertEquals("/some/path", $method->invokeArgs($request, ["/some/path?query=true&string=yes"]));
    }
}
