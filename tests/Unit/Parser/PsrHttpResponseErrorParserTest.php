<?php

declare(strict_types=1);

namespace Solitus0\HttplugLoggerBundle\Tests\Unit\Parser;

use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Psr7\Utils;
use PHPUnit\Framework\TestCase;
use Solitus0\HttplugLoggerBundle\Parser\PsrHttpResponseErrorParser;

final class PsrHttpResponseErrorParserTest extends TestCase
{
    public function testParseReturnsEmptyForSuccess(): void
    {
        $response = new Response(200, [], Utils::streamFor('ok'));

        $parser = new PsrHttpResponseErrorParser();

        self::assertSame([], $parser->parse($response));
    }

    public function testParseIncludesPayloadForErrors(): void
    {
        $response = new Response(500, [], Utils::streamFor('error'));

        $parser = new PsrHttpResponseErrorParser();
        $data = $parser->parse($response);

        self::assertSame('error', $data['jsonPayload']['responsePayload']);
    }

    public function testParseSkipsUnreadableStream(): void
    {
        $resource = fopen('php://temp', 'w');
        $stream = Utils::streamFor($resource);
        $response = new Response(500, [], $stream);

        $parser = new PsrHttpResponseErrorParser();

        self::assertSame([], $parser->parse($response));
    }
}
