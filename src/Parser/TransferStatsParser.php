<?php

declare(strict_types=1);

namespace Solitus0\HttplugLoggerBundle\Parser;

use GuzzleHttp\TransferStats;
use Psr\Http\Message\RequestInterface;

class TransferStatsParser implements RequestParserInterface
{
    public function __construct(
        private readonly TransferStatsCollection $collection
    ) {
    }

    public function parse(RequestInterface $request): array
    {
        $result = [];

        $transferStats = $this->collection->get($request->getUri());
        if (!$transferStats instanceof TransferStats) {
            return $result;
        }

        $metrics = self::format($transferStats);
        $result['transfer_stats'] = $metrics;
        $result['httpRequest']['latency'] = sprintf('%fs', $metrics['total_time']);

        return $result;
    }

    public static function format(TransferStats $transferStats): array
    {
        $stats = $transferStats->getHandlerStats();

        return [
            'namelookup_time' => self::getFloatMetric($stats, 'namelookup_time'),
            'connect_time' => self::getFloatMetric($stats, 'connect_time'),
            'appconnect_time' => self::getFloatMetric($stats, 'appconnect_time'),
            'pretransfer_time' => self::getFloatMetric($stats, 'pretransfer_time'),
            'starttransfer_time' => self::getFloatMetric($stats, 'starttransfer_time'),
            'total_time' => self::getFloatMetric($stats, 'total_time'),
        ];
    }

    public static function getFloatMetric(array $data, string $key): float
    {
        return isset($data[$key]) && is_numeric($data[$key]) ? (float) $data[$key] : 0.0;
    }
}
