<?php

declare(strict_types=1);

namespace Solitus0\HttplugLoggerBundle\Parser;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\StreamInterface;

class PsrHttpRequestGraphqlPayloadParser implements RequestParserInterface
{
    public function parse(RequestInterface $request): array
    {
        $result = [];

        $requestPayload = $this->getRequestPayload($request);
        if ($requestPayload) {
            $decodedPayload = json_decode($requestPayload, true);
            $result['jsonPayload']['requestPayload'] = $decodedPayload;
        }

        return $result;
    }

    private function getRequestPayload(RequestInterface $request): string
    {
        $body = (clone $request)->getBody();
        $content = $this->readFromStream($body);

        return $this->sanitizeContent($content);
    }

    private function readFromStream(StreamInterface $body): string
    {
        if ($body->isSeekable()) {
            $previousPosition = $body->tell();
            $body->rewind();
        }

        $content = $body->getContents();

        if (isset($previousPosition) && $body->isSeekable()) {
            $body->seek($previousPosition);
        }

        return $content;
    }

    private function sanitizeContent(string $content): string
    {
        return $content ? str_replace(chr(0), '', $content) : $content;
    }
}
