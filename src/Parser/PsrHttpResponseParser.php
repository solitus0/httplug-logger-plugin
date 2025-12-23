<?php

declare(strict_types=1);

namespace Solitus0\HttplugLoggerBundle\Parser;

use Psr\Http\Message\ResponseInterface;

class PsrHttpResponseParser implements ResponseParserInterface
{
    public function parse(ResponseInterface $response): array
    {
        return [
            'httpRequest' => [
                'status' => $this->getStatusCode($response),
                'responseSize' => $this->getResponseBodySize($response),
            ],
        ];
    }

    private function getStatusCode(ResponseInterface $response): int
    {
        return $response->getStatusCode();
    }

    private function getResponseBodySize(ResponseInterface $response): string
    {
        $bodySize = $response->getBody()->getSize();
        $responseHeadersSize = strlen(json_encode($response->getHeaders()));

        return (string) ($bodySize + $responseHeadersSize);
    }
}
