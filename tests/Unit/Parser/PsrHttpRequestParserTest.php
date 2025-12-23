<?php

declare(strict_types=1);

namespace Solitus0\HttplugLoggerBundle\Tests\Unit\Parser;

use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Utils;
use PHPUnit\Framework\TestCase;
use Solitus0\HttplugLoggerBundle\Parser\PsrHttpRequestParser;

final class PsrHttpRequestParserTest extends TestCase
{
    public function testParseIncludesMethodUrlSizeAndHeaders(): void
    {
        $body = Utils::streamFor('abc');
        $request = new Request('POST', 'https://example.com/test#fragment', ['X-Test' => ['a', 'b']], $body);

        $parser = new PsrHttpRequestParser();
        $data = $parser->parse($request);

        self::assertSame('POST', $data['httpRequest']['requestMethod']);
        self::assertSame('https://example.com/test', $data['httpRequest']['requestUrl']);
        self::assertSame('a, b', $data['jsonPayload']['requestHeaders']['X-Test']);

        $expectedSize = (string) ($body->getSize() + strlen(json_encode($request->getHeaders())));
        self::assertSame($expectedSize, $data['httpRequest']['requestSize']);
    }
}
