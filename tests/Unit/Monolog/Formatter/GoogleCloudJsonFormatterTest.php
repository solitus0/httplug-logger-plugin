<?php

declare(strict_types=1);

namespace Solitus0\HttplugLoggerBundle\Tests\Unit\Monolog\Formatter;

use Monolog\Level;
use Monolog\LogRecord;
use PHPUnit\Framework\TestCase;
use Solitus0\HttplugLoggerBundle\Monolog\Formatter\GoogleCloudJsonFormatter;

final class GoogleCloudJsonFormatterTest extends TestCase
{
    public function testFormatBuildsGoogleCloudCompatiblePayload(): void
    {
        $formatter = new GoogleCloudJsonFormatter();
        $date = new \DateTimeImmutable('2024-01-01T12:00:00+00:00');

        $record = new LogRecord(
            $date,
            'channel',
            Level::Warning,
            'message',
            [
                'httpRequest' => ['status' => 200],
                'jsonPayload' => ['foo' => 'bar'],
                'other' => 'value',
            ],
            [
                'remoteIp' => '127.0.0.1',
                'trace' => 'projects/test/traces/abc',
            ]
        );

        $payload = json_decode($formatter->format($record), true);

        self::assertSame('WARNING', $payload['severity']);
        self::assertSame($date->format(\DateTimeInterface::RFC3339_EXTENDED), $payload['time']);
        self::assertSame('projects/test/traces/abc', $payload['logging.googleapis.com/trace']);
        self::assertTrue($payload['logging.googleapis.com/trace_sampled']);
        self::assertSame(['status' => 200, 'remoteIp' => '127.0.0.1'], $payload['httpRequest']);
        self::assertSame('bar', $payload['foo']);
        self::assertSame('value', $payload['other']);

        self::assertArrayNotHasKey('context', $payload);
        self::assertArrayNotHasKey('extra', $payload);
        self::assertArrayNotHasKey('level', $payload);
        self::assertArrayNotHasKey('level_name', $payload);
        self::assertArrayNotHasKey('datetime', $payload);
    }
}
