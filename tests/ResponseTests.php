<?php
declare(strict_types=1);

namespace TJCDev\Router\Tests;

use PHPUnit\Framework\TestCase;
use ReflectionClass;
use TJCDev\Router\Exceptions\InvalidResponseStatusCodeException;
use TJCDev\Router\Response;
use const JSON_NUMERIC_CHECK;
use const JSON_UNESCAPED_SLASHES;
use const JSON_UNESCAPED_UNICODE;

class ResponseTests extends TestCase
{
    public function testSetMethods(): void
    {
        $body = 'This page is gone';
        $code = 410;
        $header = ['key' => 'Server', 'value' => 'Nginx'];
        $headerReplaceValue = 'Apache';
        $response = new Response();
        $response->setBody($body)->setCode($code)->addHeader($header['key'], $header['value']);
        $reflection = new ReflectionClass($response);

        $property = $reflection->getProperty('body');
        $property->setAccessible(true);
        $this->assertEquals($body, $property->getValue($response), "Response::setBody() should store the body as given.");

        $property = $reflection->getProperty('code');
        $property->setAccessible(true);
        $this->assertEquals($code, $property->getValue($response), "Response::setCode() should store the valid response code as given.");

        $property = $reflection->getProperty('headers');
        $property->setAccessible(true);
        $this->assertEquals([$header['key'] => $header['value']], $property->getValue($response), "Response::addHeader() should store a valid response header in an array.");

        $response->addHeader($header['key'], $headerReplaceValue);
        $this->assertEquals([$header['key'] => $headerReplaceValue], $property->getValue($response), "Response::addHeader() should replace an existing headers value with the given one.");
    }

    /**
     * @runInSeparateProcess
     */
    public function testSendResponse(): void
    {
        $body = ['data' => ['alive' => true]];
        $json = json_encode($body, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_NUMERIC_CHECK);
        $length = mb_strlen($json, '8bit');
        $expectedHeaders = [
            "Content-Type: application/json",
            "Content-Length: $length",
        ];
        $response = new Response();
        $response->setBody($body);

        $this->expectOutputString($json);
        $response->sendResponse();
        $this->assertEquals($expectedHeaders, xdebug_get_headers(), "Response::sendResponse() should output the correct response headers.");
    }

    public function testErrorOnInvalidResponseCode(): void
    {
        $this->expectException(InvalidResponseStatusCodeException::class);
        $this->expectExceptionMessage('"1" is not a valid response status code.');
        $this->expectExceptionCode(500);
        $response = new Response();
        $response->setCode(1);
    }
}
