<?php

declare(strict_types=1);

namespace Solitus0\HttplugLoggerBundle\Parser;

use Psr\Http\Message\RequestInterface;

interface RequestParserInterface
{
    public function parse(RequestInterface $request): array;
}
