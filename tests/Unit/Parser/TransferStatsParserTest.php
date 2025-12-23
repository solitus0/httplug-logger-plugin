<?php

declare(strict_types=1);

namespace Solitus0\HttplugLoggerBundle\Tests\Unit\Parser;

use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Uri;
use GuzzleHttp\TransferStats;
use PHPUnit\Framework\TestCase;
use Solitus0\HttplugLoggerBundle\Parser\TransferStatsCollection;
use Solitus0\HttplugLoggerBundle\Parser\TransferStatsParser;

final class TransferStatsParserTest extends TestCase
{
    public function testParseReturnsMetricsAndLatency(): void
    {
        $collection = new TransferStatsCollection();
        $uri = new Uri('https://example.com');
        $request = new Request('GET', $uri);

        $stats = new TransferStats($request, null, null, null, [
            'namelookup_time' => '0.01',
            'connect_time' => 0.02,
            'appconnect_time' => null,
            'pretransfer_time' => 0.03,
            'starttransfer_time' => 0.04,
            'total_time' => 0.05,
        ]);

        $collection->add($uri, $stats);

        $parser = new TransferStatsParser($collection);
        $data = $parser->parse($request);

        self::assertSame(0.01, $data['transfer_stats']['namelookup_time']);
        self::assertSame(0.02, $data['transfer_stats']['connect_time']);
        self::assertSame(0.0, $data['transfer_stats']['appconnect_time']);
        self::assertSame('0.050000s', $data['httpRequest']['latency']);
    }

    public function testParseReturnsEmptyWhenNoStats(): void
    {
        $collection = new TransferStatsCollection();
        $request = new Request('GET', 'https://example.com');

        $parser = new TransferStatsParser($collection);

        self::assertSame([], $parser->parse($request));
    }
}
