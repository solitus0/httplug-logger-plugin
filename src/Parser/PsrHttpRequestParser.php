<?php

declare(strict_types=1);

namespace Solitus0\HttplugLoggerBundle\Parser;

use Psr\Http\Message\RequestInterface;

class PsrHttpRequestParser implements RequestParserInterface
{
    public function parse(RequestInterface $request): array
    {
        return [
            'httpRequest' => [
                'requestMethod' => $request->getMethod(),
                'requestUrl' => (string) $request->getUri()->withFragment(''),
                'requestSize' => $this->getRequestSize($request),
            ],
            'jsonPayload' => [
                'requestHeaders' => $this->getHeaders($request),
            ],
        ];
    }

    public function getRequestSize(RequestInterface $request): string
    {
        $bodySize = $request->getBody()->getSize() ?? 0;
        $headersSize = strlen(json_encode($request->getHeaders()));

        return (string) ($bodySize + $headersSize);
    }

    public function getHeaders(RequestInterface $request): array
    {
        $headers = $request->getHeaders();

        $flattenedHeaders = [];
        foreach ($headers as $key => $value) {
            $flattenedHeaders[$key] = implode(', ', $value);
        }

        return $flattenedHeaders;
    }
}
