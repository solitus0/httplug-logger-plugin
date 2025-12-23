<?php

declare(strict_types=1);

namespace Solitus0\HttplugLoggerBundle\Parser;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

class PsrHttpMessageParserCollection
{
    private array $requestParsers = [];

    private array $responseParsers = [];

    private array $exceptionParsers = [];

    public function __construct(
        iterable $parsers = [],
    ) {
        foreach ($parsers as $parser) {
            if ($parser instanceof RequestParserInterface) {
                $this->requestParsers[] = $parser;
            } elseif ($parser instanceof ResponseParserInterface) {
                $this->responseParsers[] = $parser;
            } elseif ($parser instanceof ExceptionParserInterface) {
                $this->exceptionParsers[] = $parser;
            } else {
                throw new \InvalidArgumentException('Unknown parser type');
            }
        }
    }

    public function parse(
        RequestInterface $request,
        ?ResponseInterface $response = null,
        ?\Throwable $throwable = null,
    ): array {
        $result = [];

        /** @var RequestParserInterface $requestParser */
        foreach ($this->requestParsers as $requestParser) {
            $result = array_merge_recursive($result, $requestParser->parse($request));
        }

        if ($response instanceof ResponseInterface) {
            /** @var ResponseParserInterface $responseParser */
            foreach ($this->responseParsers as $responseParser) {
                $result = array_merge_recursive($result, $responseParser->parse($response));
            }
        }

        if ($throwable instanceof \Exception) {
            /** @var ExceptionParserInterface $exceptionParser */
            foreach ($this->exceptionParsers as $exceptionParser) {
                $result = array_merge_recursive($result, $exceptionParser->parse($throwable));
            }
        }

        return $result;
    }
}
