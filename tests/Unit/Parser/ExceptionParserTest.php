<?php

declare(strict_types=1);

namespace Solitus0\HttplugLoggerBundle\Tests\Unit\Parser;

use PHPUnit\Framework\TestCase;
use Solitus0\HttplugLoggerBundle\Parser\ExceptionParser;

final class ExceptionParserTest extends TestCase
{
    public function testParseIncludesExceptionContext(): void
    {
        $exception = new class('failure', 123) extends \Exception {
            public function getHandlerContext(): array
            {
                return ['errno' => 42];
            }
        };

        $parser = new ExceptionParser();
        $data = $parser->parse($exception);

        self::assertSame(500, $data['httpRequest']['status']);
        self::assertSame(123, $data['jsonPayload']['exception']['code']);
        self::assertSame('failure', $data['jsonPayload']['exception']['message']);
        self::assertSame(get_class($exception), $data['jsonPayload']['exception']['class']);
        self::assertSame(['errno' => 42], $data['jsonPayload']['exception']['context']);
    }
}
