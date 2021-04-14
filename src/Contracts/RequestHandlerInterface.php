<?php
declare(strict_types=1);

namespace TJCDev\Router\Contracts;

interface RequestHandlerInterface
{
    public function handle(RequestInterface $request): ResponseInterface;
    public function registerRouter(RouterInterface $router): RequestHandlerInterface;
}
