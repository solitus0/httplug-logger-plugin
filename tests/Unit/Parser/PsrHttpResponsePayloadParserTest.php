<?php

declare(strict_types=1);

namespace Solitus0\HttplugLoggerBundle\Tests\Unit\Parser;

use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Psr7\Utils;
use PHPUnit\Framework\TestCase;
use Solitus0\HttplugLoggerBundle\Parser\PsrHttpResponsePayloadParser;

final class PsrHttpResponsePayloadParserTest extends TestCase
{
    public function testParseReturnsDecodedJsonPayload(): void
    {
        $response = new Response(200, [], Utils::streamFor('{"a":1}'));

        $parser = new PsrHttpResponsePayloadParser();
        $data = $parser->parse($response);

        self::assertSame(['a' => 1], $data['jsonPayload']['responsePayload']);
    }

    public function testParseTruncatesLargePayload(): void
    {
        $response = new Response(200, [], Utils::streamFor('abc'));

        $parser = new PsrHttpResponsePayloadParser(true, 0);
        $data = $parser->parse($response);

        self::assertSame(
            ['message' => 'Log was truncated. Response content exceeds 0KB limit', 'size' => 3],
            $data['jsonPayload']['responsePayload']
        );
    }

    public function testParseRemovesPayloadWhenContentDispositionPresent(): void
    {
        $response = new Response(200, ['content-disposition' => 'attachment; filename="a.txt"'], Utils::streamFor('abc'));

        $parser = new PsrHttpResponsePayloadParser();
        $data = $parser->parse($response);

        self::assertSame('File content was removed from the log.', $data['jsonPayload']['responsePayload']);
    }
}
