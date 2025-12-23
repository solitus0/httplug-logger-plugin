<?php

declare(strict_types=1);

namespace Solitus0\HttplugLoggerBundle\Tests\Unit\Monolog\Processor;

use Monolog\Level;
use Monolog\LogRecord;
use PHPUnit\Framework\TestCase;
use Solitus0\HttplugLoggerBundle\Monolog\Processor\RequestDecoratorProcessor;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

final class RequestDecoratorProcessorTest extends TestCase
{
    public function testProcessorAddsRequestMetadataAndCaches(): void
    {
        $request = Request::create('https://example.com/path', 'GET', [], [], [], [
            'HTTP_REFERER' => 'https://referer.test',
            'REMOTE_ADDR' => '127.0.0.1',
            'HTTP_USER_AGENT' => 'UnitTest',
            'HTTP_HOST' => 'example.com',
        ]);

        $stack = new RequestStack();
        $stack->push($request);

        $processor = new RequestDecoratorProcessor($stack);

        $record = new LogRecord(
            new \DateTimeImmutable('2024-01-01T00:00:00+00:00'),
            'test',
            Level::Info,
            'message',
            [],
            []
        );

        $first = $processor($record);

        self::assertSame('https://referer.test', $first['extra']['referer']);
        self::assertSame('/path', $first['extra']['route']);
        self::assertSame('127.0.0.1', $first['extra']['remoteIp']);
        self::assertSame('example.com', $first['extra']['host']);
        self::assertSame('UnitTest', $first['extra']['userAgent']);

        $stack->pop();

        $second = $processor(new LogRecord(
            new \DateTimeImmutable('2024-01-01T00:00:00+00:00'),
            'test',
            Level::Info,
            'message',
            [],
            []
        ));

        self::assertSame($first['extra'], $second['extra']);
    }
}
