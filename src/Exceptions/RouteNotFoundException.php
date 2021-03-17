<?php
declare(strict_types = 1);

namespace TJCDev\Router\Exceptions;

class RouteNotFoundException extends \Exception
{
    protected $code = 404;
    protected $message = "Route not found";
}
