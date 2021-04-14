<?php
declare(strict_types=1);

namespace TJCDev\Router;

use TJCDev\Router\Contracts\ResponseInterface;
use TJCDev\Router\Exceptions\InvalidResponseStatusCodeException;
use const JSON_ERROR_NONE;
use const JSON_NUMERIC_CHECK;
use const JSON_UNESCAPED_SLASHES;
use const JSON_UNESCAPED_UNICODE;

class Response implements ResponseInterface
{
    public const VALID_RESPONSE_CODES = [100, 101, 200, 201, 202, 204, 205, 301, 302, 303, 304,
        307, 308, 400, 401, 403, 404, 405, 406, 407, 408, 409, 410, 411, 412, 413, 414, 415, 416, 417, 426, 428, 429,
        431, 500, 501, 502, 503];
    public const RESPONSE_CODE_100 = "100 Continue";
    public const RESPONSE_CODE_101 = "101 Switching Protocol";
    public const RESPONSE_CODE_200 = "200 OK";
    public const RESPONSE_CODE_201 = "201 Created";
    public const RESPONSE_CODE_202 = "202 Accepted";
    public const RESPONSE_CODE_204 = "204 No Content";
    public const RESPONSE_CODE_205 = "205 Reset Content";
    public const RESPONSE_CODE_301 = "301 Moved Permanently";
    public const RESPONSE_CODE_302 = "302 Found";
    public const RESPONSE_CODE_303 = "303 See Other";
    public const RESPONSE_CODE_304 = "304 Not Modified";
    public const RESPONSE_CODE_307 = "307 Temporary Redirect";
    public const RESPONSE_CODE_308 = "308 Permanent Redirect";
    public const RESPONSE_CODE_400 = "400 Bad Request";
    public const RESPONSE_CODE_401 = "401 Unauthorized";
    public const RESPONSE_CODE_403 = "403 Forbidden";
    public const RESPONSE_CODE_404 = "404 Not Found";
    public const RESPONSE_CODE_405 = "405 Method Not Allowed";
    public const RESPONSE_CODE_406 = "406 Not Acceptable";
    public const RESPONSE_CODE_407 = "407 Proxy Authentication Required";
    public const RESPONSE_CODE_408 = "408 Request Timeout";
    public const RESPONSE_CODE_409 = "409 Conflict";
    public const RESPONSE_CODE_410 = "410 Gone";
    public const RESPONSE_CODE_411 = "411 Length Required";
    public const RESPONSE_CODE_412 = "412 Precondition Failed";
    public const RESPONSE_CODE_413 = "413 Payload Too Large";
    public const RESPONSE_CODE_414 = "414 URI Too Long";
    public const RESPONSE_CODE_415 = "415 Unsupported Media Type";
    public const RESPONSE_CODE_416 = "416 Range Not Satisfiable";
    public const RESPONSE_CODE_417 = "417 Expectation Failed";
    public const RESPONSE_CODE_426 = "426 Upgrade Required";
    public const RESPONSE_CODE_428 = "428 Precondition Required";
    public const RESPONSE_CODE_429 = "429 Too Many Requests";
    public const RESPONSE_CODE_431 = "431 Request header Fields Too Large";
    public const RESPONSE_CODE_500 = "500 Internal Server Error";
    public const RESPONSE_CODE_501 = "501 Not Implemented";
    public const RESPONSE_CODE_502 = "502 Bad Gateway";
    public const RESPONSE_CODE_503 = "503 Service Unavailable";

    protected mixed $body;
    protected int $code = 200;
    protected string $contentType = 'application/json';
    protected array $headers = [];

    /**
     * @inheritDoc
     */
    public function setBody($body): ResponseInterface
    {
        $this->body = $body;
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function addHeader(string $header, string $value): ResponseInterface
    {
        // todo: validate header
        $this->headers[$header] = $value;

        if ($header == 'Content-Type') {
            $this->contentType = $value;
        }

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function setCode(int $code): ResponseInterface
    {
        if ( ! in_array($code, self::VALID_RESPONSE_CODES))
            throw new InvalidResponseStatusCodeException(sprintf('"%d" is not a valid response status code.', $code),
                500);

        $this->code = $code;
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function sendResponse(): void
    {
        $this->prepareBody();
        $this->setContentTypeHeader();
        $this->setContentLengthHeader();
        header("HTTP/1.1 " . constant(self::class . "::RESPONSE_CODE_{$this->code}"));
        foreach ($this->headers as $header => $value) {
            header("$header: $value");
        }
        echo $this->body;
    }

    protected function prepareBody(): void
    {
        if (is_array($this->body) && $this->contentType == 'application/json') {
            $this->body = json_encode($this->body, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_NUMERIC_CHECK);
        }
    }

    protected function setContentTypeHeader(): void
    {
        if ( ! array_key_exists('Content-Type', $this->headers)) {
            $this->addHeader('Content-Type', $this->contentType);
        }
    }

    protected function setContentLengthHeader(): void
    {
        if ($this->isValidJson($this->body)) {
            $length = mb_strlen($this->body, '8bit');
        } else {
            $length = strlen($this->body);
        }

        $this->addHeader('Content-Length', (string) $length);
    }

    protected function isValidJson($string): bool
    {
        json_decode($string);
        return json_last_error() == JSON_ERROR_NONE;
    }
}
