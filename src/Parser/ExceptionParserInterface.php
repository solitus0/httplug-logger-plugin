<?php

declare(strict_types=1);

namespace Solitus0\HttplugLoggerBundle\Parser;

interface ExceptionParserInterface
{
    public function parse(\Exception $e): array;
}
