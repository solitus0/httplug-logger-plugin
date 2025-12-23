<?php

declare(strict_types=1);

namespace Solitus0\HttplugLoggerBundle\Tests\Unit\Parser;

use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Utils;
use PHPUnit\Framework\TestCase;
use Solitus0\HttplugLoggerBundle\Parser\PsrHttpRequestGraphqlPayloadParser;

final class PsrHttpRequestGraphqlPayloadParserTest extends TestCase
{
    public function testParseDecodesJsonPayload(): void
    {
        $payload = json_encode(['query' => '{ me { id } }', 'variables' => ['id' => 1]]);
        $request = new Request('POST', 'https://example.com/graphql', [], Utils::streamFor($payload));

        $parser = new PsrHttpRequestGraphqlPayloadParser();
        $data = $parser->parse($request);

        self::assertSame(['query' => '{ me { id } }', 'variables' => ['id' => 1]], $data['jsonPayload']['requestPayload']);
    }
}
