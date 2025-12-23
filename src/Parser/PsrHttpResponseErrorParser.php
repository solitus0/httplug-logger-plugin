<?php

declare(strict_types=1);

namespace Solitus0\HttplugLoggerBundle\Parser;

use Psr\Http\Message\ResponseInterface;

class PsrHttpResponseErrorParser implements ResponseParserInterface
{
    public function __construct(
        private int $maxKilobyteSize = 7
    ) {
    }

    public function parse(ResponseInterface $response): array
    {
        $result = [];

        $statusCode = $this->getStatusCode($response);
        if ($statusCode >= 200 && $statusCode < 300) {
            return $result;
        }

        $responseBody = $this->getResponseBody($response);
        if ($responseBody) {
            $result['jsonPayload']['responsePayload'] = $responseBody;
        }

        return $result;
    }

    private function getStatusCode(ResponseInterface $response): int
    {
        return $response->getStatusCode();
    }

    private function getResponseBody(ResponseInterface $response)
    {
        $body = (clone $response)->getBody();
        if (!$body->isReadable()) {
            return '';
        }

        if ($response->hasHeader('content-disposition')) {
            return 'File content was removed from the log.';
        }

        $previousPosition = 0;
        if ($body->isSeekable()) {
            $previousPosition = $body->tell();
            $body->rewind();
        }

        $content = $body->getContents();
        if ($body->isSeekable()) {
            $body->seek($previousPosition);
        }

        $isGreaterThanLimit = $this->isGreaterThanLimit($content);
        if ($isGreaterThanLimit) {
            $content = [
                'message' => sprintf('Log was truncated. Response content exceeds %dKB limit', $this->maxKilobyteSize),
                'size' => strlen($content),
            ];
        }

        if (is_string($content) && json_decode($content)) {
            return json_decode($content, true);
        }

        return $content;
    }

    private function isGreaterThanLimit(string $string): bool
    {
        $byteSize = strlen($string);
        $kilobyteSize = $byteSize / 1024;

        return $kilobyteSize > $this->maxKilobyteSize;
    }
}
