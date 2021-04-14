<?php
declare(strict_types=1);

namespace TJCDev\Router;

use JetBrains\PhpStorm\Pure;
use TJCDev\Router\Contracts\RequestInterface;
use TJCDev\Router\Exceptions\InvalidHTTPMethodException;

class Request implements RequestInterface
{
    const HTTP_METHODS = ['CONNECT', 'DELETE', 'GET', 'HEAD', 'OPTIONS', 'PATCH', 'POST', 'PUT', 'TRACE'];

    protected string $method;
    protected string $path;
    protected array $query;
    protected array $request;

    /**
     * @param string  $method
     * @param string  $uri
     * @param array   $query
     * @param array   $request
     *
     * @throws InvalidHTTPMethodException
     */
    public function __construct(string $method, string $uri, array $query = [], array $request = [])
    {
        $method = strtoupper($method);
        if ( ! in_array($method, Request::HTTP_METHODS))
            throw new InvalidHTTPMethodException(sprintf('"%s" is an invalid HTTP method.', $method));

        $this->method = $method;
        $this->path = $this->stripQueryString($uri);
        $this->query = $query;
        $this->request = $request;
    }

    /**
     * Create a new Request object using values stored in $_SERVER.
     *
     * @return RequestInterface
     * @throws InvalidHTTPMethodException
     */
    public static function fromServer(): RequestInterface
    {
        return new Request($_SERVER['REQUEST_METHOD'], $_SERVER['REQUEST_URI'], $_GET, $_POST);
    }

    /**
     * @inheritDoc
     */
    public function getMethod(): string
    {
        return $this->method;
    }

    /**
     * @inheritDoc
     */
    public function getPath(): string
    {
        return $this->path;
    }

    /**
     * @inheritDoc
     */
    public function getQuery(): array
    {
        return $this->query;
    }

    /**
     * @inheritDoc
     */
    public function getRequest(): array
    {
        return $this->request;
    }

    /**
     * Return the URI passed in without a query string, if one was present.
     *
     * @param string  $uri
     *
     * @return string
     */
    #[Pure]
    protected function stripQueryString(string $uri): string
    {
        if (($pos = strpos($uri, "?")) !== false) {
            $uri = substr($uri, 0, $pos);
        }

        return $uri;
    }
}
