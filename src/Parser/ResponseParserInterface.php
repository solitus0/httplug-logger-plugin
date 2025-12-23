<?php

declare(strict_types=1);

namespace Solitus0\HttplugLoggerBundle\Parser;

use Psr\Http\Message\ResponseInterface;

interface ResponseParserInterface
{
    public function parse(ResponseInterface $response): array;
}
