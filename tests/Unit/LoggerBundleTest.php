<?php

declare(strict_types=1);

namespace Solitus0\HttplugLoggerBundle\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Solitus0\HttplugLoggerBundle\DependencyInjection\HttplugLoggerBundleExtension;
use Solitus0\HttplugLoggerBundle\LoggerBundle;
use Symfony\Component\DependencyInjection\ContainerBuilder;

final class LoggerBundleTest extends TestCase
{
    public function testBuildLoadsServiceDefinitions(): void
    {
        if (!class_exists('Symfony\\Component\\Yaml\\Yaml')) {
            self::markTestSkipped('symfony/yaml is not installed, skipping service load test.');
        }

        $bundle = new LoggerBundle();
        $container = new ContainerBuilder();

        $bundle->build($container);

        self::assertTrue($container->hasDefinition('httplug_logger.parser.psr_request'));
        self::assertTrue($container->hasDefinition('httplug_logger.parser.psr_response'));
    }

    public function testGetContainerExtension(): void
    {
        $bundle = new LoggerBundle();

        self::assertInstanceOf(HttplugLoggerBundleExtension::class, $bundle->getContainerExtension());
    }
}
