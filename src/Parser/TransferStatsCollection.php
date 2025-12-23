<?php

declare(strict_types=1);

namespace Solitus0\HttplugLoggerBundle\Parser;

use GuzzleHttp\TransferStats;
use Psr\Http\Message\UriInterface;

class TransferStatsCollection
{
    private array $collection = [];

    public function add(UriInterface $uri, TransferStats $transferStats): void
    {
        $this->collection[spl_object_hash($uri)] = $transferStats;
    }

    public function get(UriInterface $uri): ?TransferStats
    {
        return $this->collection[spl_object_hash($uri)] ?? null;
    }
}
