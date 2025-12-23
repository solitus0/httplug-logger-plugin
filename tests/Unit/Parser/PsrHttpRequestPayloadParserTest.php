<?php

declare(strict_types=1);

namespace Solitus0\HttplugLoggerBundle\Tests\Unit\Parser;

use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Utils;
use PHPUnit\Framework\TestCase;
use Solitus0\HttplugLoggerBundle\Parser\PsrHttpRequestPayloadParser;

final class PsrHttpRequestPayloadParserTest extends TestCase
{
    public function testParseReturnsPayloadWhenPresent(): void
    {
        $request = new Request('POST', 'https://example.com', [], Utils::streamFor('payload'));

        $parser = new PsrHttpRequestPayloadParser();
        $data = $parser->parse($request);

        self::assertSame('payload', $data['jsonPayload']['requestPayload']);
    }

    public function testParseSanitizesNullBytes(): void
    {
        $content = 'a' . chr(0) . 'b';
        $request = new Request('POST', 'https://example.com', [], Utils::streamFor($content));

        $parser = new PsrHttpRequestPayloadParser();
        $data = $parser->parse($request);

        self::assertSame('ab', $data['jsonPayload']['requestPayload']);
    }

    public function testParseMultipartFormData(): void
    {
        $boundary = '----WebKitFormBoundary7MA4YWxkTrZu0gW';
        $headers = ['Content-Type' => 'multipart/form-data; boundary=' . $boundary];
        $body = implode("\r\n", [
            '--' . $boundary,
            'Content-Disposition: form-data; name="field1"',
            '',
            'value1',
            '--' . $boundary,
            'Content-Disposition: form-data; name="file1"; filename="a.txt"',
            'Content-Type: text/plain',
            '',
            'filecontent',
            '--' . $boundary . '--',
            '',
        ]);

        $request = new Request('POST', 'https://example.com', $headers, Utils::streamFor($body));
        $parser = new PsrHttpRequestPayloadParser();

        $data = $parser->parse($request);

        self::assertSame('value1', $data['jsonPayload']['requestPayload']['field1']);
        self::assertSame('(11)', $data['jsonPayload']['requestPayload']['file1']);
    }

    public function testParseReturnsEmptyArrayWhenNoPayload(): void
    {
        $request = new Request('POST', 'https://example.com', [], Utils::streamFor(''));

        $parser = new PsrHttpRequestPayloadParser();

        self::assertSame([], $parser->parse($request));
    }
}
