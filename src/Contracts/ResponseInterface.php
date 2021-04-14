<?php
declare(strict_types=1);

namespace TJCDev\Router\Contracts;

use TJCDev\Router\Exceptions\InvalidResponseStatusCodeException;

interface ResponseInterface
{
    /**
     * Add a header to be sent as part of the response.
     *
     * @param string  $header
     * @param string  $value
     *
     * @return ResponseInterface
     */
    public function addHeader(string $header, string $value): ResponseInterface;

    /**
     * Send the response to the client.
     */
    public function sendResponse(): void;

    /**
     * Set the content for the body of the response.
     *
     * @param mixed $body
     *
     * @return ResponseInterface
     */
    public function setBody(mixed $body): ResponseInterface;

    /**
     * Set the HTTP response status code. The code MUST validate the response code.
     *
     * @param int  $code
     *
     * @return ResponseInterface
     * @throws InvalidResponseStatusCodeException
     * @link https://developer.mozilla.org/en-US/docs/Web/HTTP/Status
     */
    public function setCode(int $code): ResponseInterface;
}
