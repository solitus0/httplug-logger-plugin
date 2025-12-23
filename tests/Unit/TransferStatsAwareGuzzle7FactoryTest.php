<?php

declare(strict_types=1);

namespace Solitus0\HttplugLoggerBundle\Tests\Unit;

use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Uri;
use GuzzleHttp\RequestOptions;
use GuzzleHttp\TransferStats;
use PHPUnit\Framework\TestCase;
use Solitus0\HttplugLoggerBundle\Parser\TransferStatsCollection;
use Solitus0\HttplugLoggerBundle\TransferStatsAwareGuzzle7Factory;

final class TransferStatsAwareGuzzle7FactoryTest extends TestCase
{
    public function testCreateClientRegistersOnStatsHandler(): void
    {
        $collection = new TransferStatsCollection();
        $factory = new TransferStatsAwareGuzzle7Factory($collection);

        $client = $factory->createClient(['timeout' => 1.0]);

        $reflection = new \ReflectionProperty($client, 'guzzle');
        $reflection->setAccessible(true);
        $guzzle = $reflection->getValue($client);

        $onStats = $guzzle->getConfig(RequestOptions::ON_STATS);
        self::assertIsCallable($onStats);

        $uri = new Uri('https://example.com');
        $request = new Request('GET', $uri);
        $stats = new TransferStats($request);

        $onStats($stats);

        self::assertSame($stats, $collection->get($uri));
    }
}
