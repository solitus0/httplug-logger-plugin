<?php

declare(strict_types=1);

namespace Solitus0\HttplugLoggerBundle\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;
use Solitus0\HttplugLoggerBundle\LevelPicker\LogLevelPicker;
use Solitus0\HttplugLoggerBundle\LoggerPlugin;
use Solitus0\HttplugLoggerBundle\LoggerPluginFactory;
use Solitus0\HttplugLoggerBundle\Parser\PsrHttpMessageParserCollection;

final class LoggerPluginFactoryTest extends TestCase
{
    public function testBuildReturnsLoggerPlugin(): void
    {
        $collection = new PsrHttpMessageParserCollection([]);
        $levelPicker = new LogLevelPicker();
        $logger = new NullLogger();

        $plugin = LoggerPluginFactory::build($collection, $levelPicker, $logger, 'client');

        self::assertInstanceOf(LoggerPlugin::class, $plugin);
    }
}
