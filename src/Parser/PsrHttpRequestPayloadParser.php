<?php

declare(strict_types=1);

namespace Solitus0\HttplugLoggerBundle\Parser;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\StreamInterface;

class PsrHttpRequestPayloadParser implements RequestParserInterface
{
    public function parse(RequestInterface $request): array
    {
        $requestPayload = $this->getRequestPayload($request);
        $result = [];
        if ($requestPayload) {
            $result['jsonPayload']['requestPayload'] = $requestPayload;
        }

        return $result;
    }

    private function getRequestPayload(RequestInterface $request): array|string
    {
        $content = $this->extractContent($request);

        if ($this->isMultipartFormData($request)) {
            return $this->parseMultipartFormData($request, $content);
        }

        return $content;
    }

    private function extractContent(RequestInterface $request): string
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
        return $content !== '' && $content !== '0' ? str_replace(chr(0), '', $content) : $content;
    }

    private function isMultipartFormData(RequestInterface $request): bool
    {
        $contentType = $request->getHeaderLine('Content-Type');

        return str_contains($contentType, 'multipart/form-data');
    }

    private function parseMultipartFormData(RequestInterface $request, string $content): array
    {
        $boundary = $this->extractBoundary($request->getHeaderLine('Content-Type'));
        $parts = preg_split('/--' . preg_quote($boundary, '/') . '/', $content);

        $nestedArray = array_values(
            array_filter(
                array_map(function (string $part): ?array {
                    return $this->parseFormDataPart($part);
                }, $parts)
            )
        );

        return array_merge(...$nestedArray);
    }

    private function extractBoundary(string $contentType): string
    {
        return str_replace('multipart/form-data; boundary=', '', $contentType);
    }

    private function parseFormDataPart(string $part): ?array
    {
        $part = trim($part);
        if ($part === '' || $part === '0' || $part === '--') {
            return null;
        }

        $partLines = preg_split("/\r\n/", $part);
        $partLines = array_filter($partLines, static fn ($line): bool => !in_array(trim($line), ['', '0'], true));

        $partData = [];
        foreach ($partLines as $line) {
            if (str_contains($line, ':')) {
                [$key, $value] = explode(':', $line, 2);
                $partData[trim($key)] = trim($value);
            }
        }

        $disposition = $partData['Content-Disposition'];
        $key = $this->parseFieldName($disposition);
        if ($this->isFilePart($part)) {
            return [$key => sprintf('(%s)', strlen(end($partLines)))];
        }

        return [$key => end($partLines)];
    }

    private function parseFieldName(string $inputString): ?string
    {
        $pattern = '/name="([^"]+)"/';
        if (preg_match($pattern, $inputString, $matches)) {
            return $matches[1];
        }

        return null;
    }

    private function isFilePart(string $part): bool
    {
        return (bool) preg_match('/Content-Disposition: form-data;.*filename="/i', $part);
    }
}
