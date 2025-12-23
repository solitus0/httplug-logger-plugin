<?php

declare(strict_types=1);

namespace Solitus0\HttplugLoggerBundle\Tests\Unit\Parser;

use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Psr7\Utils;
use PHPUnit\Framework\TestCase;
use Solitus0\HttplugLoggerBundle\Parser\PsrHttpResponseParser;

final class PsrHttpResponseParserTest extends TestCase
{
    public function testParseReturnsStatusAndResponseSize(): void
    {
        $body = Utils::streamFor('ok');
        $response = new Response(201, ['X-Test' => 'value'], $body);

        $parser = new PsrHttpResponseParser();
        $data = $parser->parse($response);

        self::assertSame(201, $data['httpRequest']['status']);

        $expectedSize = (string) ($body->getSize() + strlen(json_encode($response->getHeaders())));
        self::assertSame($expectedSize, $data['httpRequest']['responseSize']);
    }
}
