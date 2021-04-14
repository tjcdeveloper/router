<?php
declare(strict_types=1);

namespace TJCDev\Router\Tests;

use PHPUnit\Framework\MockObject\MockBuilder;
use TJCDev\Router\Request;

trait MockRouteTrait
{
    abstract public function getMockBuilder(string $class): MockBuilder;

    protected function createMockRequest($path, $method): Request
    {
        $request = $this->getMockBuilder(Request::class)
                        ->disableOriginalConstructor()
                        ->onlyMethods(['getMethod', 'getPath'])
                        ->getMock();
        $request->method('getMethod')->willReturn($method);
        $request->method('getPath')->willReturn($path);
        return $request;
    }
}
