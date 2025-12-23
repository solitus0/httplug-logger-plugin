<?php

declare(strict_types=1);

namespace Solitus0\HttplugLoggerBundle\Tests\Unit\DependencyInjection;

use PHPUnit\Framework\TestCase;
use Solitus0\HttplugLoggerBundle\DependencyInjection\Configuration;
use Symfony\Component\Config\Definition\Processor;

final class ConfigurationTest extends TestCase
{
    public function testDefaultConfigValues(): void
    {
        $processor = new Processor();
        $config = $processor->processConfiguration(new Configuration(), []);

        self::assertTrue($config['features']['request_decorator']);
        self::assertFalse($config['features']['gcp_trace_decorator']);
        self::assertSame([], $config['plugins']);
    }

    public function testPluginDefaultsAreApplied(): void
    {
        $processor = new Processor();
        $config = $processor->processConfiguration(new Configuration(), [[
            'plugins' => [
                'client' => [
                    'logger' => 'logger.test',
                ],
            ],
        ]]);

        self::assertSame('httplug_logger.parser_collection.default', $config['plugins']['client']['parser_collection']);
        self::assertSame('httplug_logger.level_picker.default', $config['plugins']['client']['level_picker']);
        self::assertSame('logger.test', $config['plugins']['client']['logger']);
    }
}
