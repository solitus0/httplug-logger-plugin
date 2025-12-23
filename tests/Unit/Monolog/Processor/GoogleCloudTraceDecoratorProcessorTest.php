<?php

declare(strict_types=1);

namespace Solitus0\HttplugLoggerBundle\Tests\Unit\Monolog\Processor;

use Monolog\Level;
use Monolog\LogRecord;
use PHPUnit\Framework\TestCase;
use Solitus0\HttplugLoggerBundle\Monolog\Processor\GoogleCloudTraceDecoratorProcessor;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

final class GoogleCloudTraceDecoratorProcessorTest extends TestCase
{
    public function testProcessorAddsTraceAndCaches(): void
    {
        $request = Request::create('https://example.com');
        $request->headers->set('x-cloud-trace-context', 'abc123/1;o=1');

        $stack = new RequestStack();
        $stack->push($request);

        $processor = new GoogleCloudTraceDecoratorProcessor($stack, 'project-id');

        $record = new LogRecord(
            new \DateTimeImmutable('2024-01-01T00:00:00+00:00'),
            'test',
            Level::Info,
            'message',
            [],
            []
        );

        $first = $processor($record);

        self::assertSame('projects/project-id/traces/abc123', $first['extra']['trace']);

        $stack->pop();

        $second = $processor(new LogRecord(
            new \DateTimeImmutable('2024-01-01T00:00:00+00:00'),
            'test',
            Level::Info,
            'message',
            [],
            []
        ));

        self::assertSame($first['extra']['trace'], $second['extra']['trace']);
    }
}
