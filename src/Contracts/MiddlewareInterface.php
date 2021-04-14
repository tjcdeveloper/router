<?php
declare(strict_types=1);

namespace TJCDev\Router\Contracts;

use Closure;

interface MiddlewareInterface
{
    public function handle(RequestInterface $request, callable $next): ResponseInterface;
}
