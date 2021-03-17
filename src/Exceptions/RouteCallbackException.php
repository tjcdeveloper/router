<?php
declare(strict_types=1);


namespace TJCDev\Router\Exceptions;


class RouteCallbackException extends \Exception
{
    protected $code = 500;
}
