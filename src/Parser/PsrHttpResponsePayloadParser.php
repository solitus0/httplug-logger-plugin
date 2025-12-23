<?php

declare(strict_types=1);

namespace Solitus0\HttplugLoggerBundle\Parser;

use Psr\Http\Message\ResponseInterface;

class PsrHttpResponsePayloadParser implements ResponseParserInterface
{
    public function __construct(
        private bool $truncateResponsePayload = true,
        private int $maxKilobyteSize = 7
    ) {
    }

    public function parse(ResponseInterface $response): array
    {
        $result = [];
        $responseBody = $this->getResponseBody($response);
        if ($responseBody) {
            $result['jsonPayload']['responsePayload'] = $responseBody;
        }

        return $result;
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

        if ($this->truncateResponsePayload) {
            $isGreaterThanLimit = $this->isGreaterThanLimit($content);
            if ($isGreaterThanLimit) {
                $content = [
                    'message' => sprintf('Log was truncated. Response content exceeds %dKB limit', $this->maxKilobyteSize),
                    'size' => strlen($content),
                ];
            }
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
