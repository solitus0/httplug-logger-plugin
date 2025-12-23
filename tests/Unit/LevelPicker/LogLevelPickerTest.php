<?php

declare(strict_types=1);

namespace Solitus0\HttplugLoggerBundle\Tests\Unit\LevelPicker;

use PHPUnit\Framework\TestCase;
use Solitus0\HttplugLoggerBundle\LevelPicker\LogLevelPicker;

final class LogLevelPickerTest extends TestCase
{
    public function testPickReturnsInfoForSuccessStatus(): void
    {
        $picker = new LogLevelPicker();

        self::assertSame('info', $picker->pick(['httpRequest' => ['status' => 204]]));
    }

    public function testPickReturnsWarningForClientError(): void
    {
        $picker = new LogLevelPicker();

        self::assertSame('warning', $picker->pick(['httpRequest' => ['status' => 404]]));
    }

    public function testPickReturnsErrorAndCriticalForServerErrors(): void
    {
        $picker = new LogLevelPicker();

        self::assertSame('error', $picker->pick(['httpRequest' => ['status' => 500]]));
        self::assertSame('critical', $picker->pick(['httpRequest' => ['status' => 502]]));
    }

    public function testPickDefaultsToInfoWhenNoStatus(): void
    {
        $picker = new LogLevelPicker();

        self::assertSame('info', $picker->pick([]));
    }
}
