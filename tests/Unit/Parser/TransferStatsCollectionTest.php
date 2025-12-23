<?php

declare(strict_types=1);

namespace Solitus0\HttplugLoggerBundle\Tests\Unit\Parser;

use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Uri;
use GuzzleHttp\TransferStats;
use PHPUnit\Framework\TestCase;
use Solitus0\HttplugLoggerBundle\Parser\TransferStatsCollection;

final class TransferStatsCollectionTest extends TestCase
{
    public function testAddAndGetReturnsTransferStats(): void
    {
        $collection = new TransferStatsCollection();
        $uri = new Uri('https://example.com');
        $stats = new TransferStats(new Request('GET', $uri));

        $collection->add($uri, $stats);

        self::assertSame($stats, $collection->get($uri));
        self::assertNull($collection->get(new Uri('https://example.com/other')));
    }
}
