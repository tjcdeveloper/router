<?php
declare(strict_types=1);

namespace TJCDev\Router\Tests;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\UriInterface;

trait MakeRequestTrait
{
    protected function makeRequestStub(string $path, string $method): RequestInterface
    {
        $uriStub = $this->createStub(UriInterface::class);
        $uriStub->method('getPath')
            ->willReturn($path);
        $requestStub = $this->createStub(RequestInterface::class);
        $requestStub->method('getMethod')
            ->willReturn($method);
        $requestStub->method('getUri')
            ->willReturn($uriStub);

        return $requestStub;
    }
}
