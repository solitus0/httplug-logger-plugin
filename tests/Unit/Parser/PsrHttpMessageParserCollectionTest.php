<?php

declare(strict_types=1);

namespace Solitus0\HttplugLoggerBundle\Tests\Unit\Parser;

use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Solitus0\HttplugLoggerBundle\Parser\ExceptionParserInterface;
use Solitus0\HttplugLoggerBundle\Parser\PsrHttpMessageParserCollection;
use Solitus0\HttplugLoggerBundle\Parser\RequestParserInterface;
use Solitus0\HttplugLoggerBundle\Parser\ResponseParserInterface;

final class PsrHttpMessageParserCollectionTest extends TestCase
{
    public function testParseMergesRequestResponseAndExceptionData(): void
    {
        $requestParser = new class() implements RequestParserInterface {
            public function parse(
                RequestInterface $request
            ): array {
                return ['httpRequest' => ['method' => $request->getMethod()]];
            }
        };

        $responseParser = new class() implements ResponseParserInterface {
            public function parse(
                ResponseInterface $response
            ): array {
                return ['httpRequest' => ['status' => $response->getStatusCode()]];
            }
        };

        $exceptionParser = new class() implements ExceptionParserInterface {
            public function parse(\Exception $e): array
            {
                return ['jsonPayload' => ['error' => $e->getMessage()]];
            }
        };

        $collection = new PsrHttpMessageParserCollection([$requestParser, $responseParser, $exceptionParser]);

        $request = new Request('GET', 'https://example.com');
        $response = new Response(404);
        $exception = new \Exception('boom');

        $data = $collection->parse($request, $response, $exception);

        self::assertSame('GET', $data['httpRequest']['method']);
        self::assertSame(404, $data['httpRequest']['status']);
        self::assertSame('boom', $data['jsonPayload']['error']);
    }

    public function testParseIgnoresThrowablesThatAreNotExceptions(): void
    {
        $exceptionParser = new class() implements ExceptionParserInterface {
            public function parse(\Exception $e): array
            {
                return ['jsonPayload' => ['error' => $e->getMessage()]];
            }
        };

        $collection = new PsrHttpMessageParserCollection([$exceptionParser]);

        $request = new Request('GET', 'https://example.com');
        $data = $collection->parse($request, null, new \Error('boom'));

        self::assertSame([], $data);
    }

    public function testConstructorThrowsOnUnknownParserType(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        new PsrHttpMessageParserCollection([new \stdClass()]);
    }
}
