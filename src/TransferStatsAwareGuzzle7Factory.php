<?php

declare(strict_types=1);

namespace Solitus0\HttplugLoggerBundle;

use GuzzleHttp\RequestOptions;
use GuzzleHttp\TransferStats;
use Http\Adapter\Guzzle7\Client;
use Solitus0\HttplugLoggerBundle\Parser\TransferStatsCollection;

class TransferStatsAwareGuzzle7Factory
{
    public function __construct(
        private TransferStatsCollection $transferStatsCollection
    ) {
    }

    public function createClient(array $config = []): Client
    {
        $transferStatsCollection = $this->transferStatsCollection;
        $config[RequestOptions::ON_STATS] = function (TransferStats $stats) use ($transferStatsCollection): void {
            $transferStatsCollection->add($stats->getRequest()->getUri(), $stats);
        };

        return Client::createWithConfig($config);
    }
}
