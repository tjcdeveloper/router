<?php
declare(strict_types=1);

namespace TJCDev\Router\Contracts;

interface RequestInterface
{
    /**
     * Get the HTTP Method used in the request.
     *
     * @return string
     */
    public function getMethod(): string;

    /**
     * Get the path part of the URI requested.
     *
     * @return string
     */
    public function getPath(): string;

    /**
     * Get the query string parameters ($_GET)
     *
     * @return array
     */
    public function getQuery(): array;

    /**
     * Get the request body parameters ($_POST)
     *
     * @return array
     */
    public function getRequest(): array;
}
