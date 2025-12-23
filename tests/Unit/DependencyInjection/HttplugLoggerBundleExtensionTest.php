<?php

declare(strict_types=1);

namespace Solitus0\HttplugLoggerBundle\Tests\Unit\DependencyInjection;

use PHPUnit\Framework\TestCase;
use Solitus0\HttplugLoggerBundle\DependencyInjection\HttplugLoggerBundleExtension;
use Solitus0\HttplugLoggerBundle\Monolog\Processor\GoogleCloudTraceDecoratorProcessor;
use Solitus0\HttplugLoggerBundle\Monolog\Processor\RequestDecoratorProcessor;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;

final class HttplugLoggerBundleExtensionTest extends TestCase
{
    public function testLoadRemovesRequestDecoratorWhenDisabled(): void
    {
        $container = new ContainerBuilder();
        $container->setDefinition(RequestDecoratorProcessor::class, new Definition(RequestDecoratorProcessor::class));

        $extension = new HttplugLoggerBundleExtension();
        $extension->load([
            ['features' => ['request_decorator' => false, 'gcp_trace_decorator' => false]],
        ], $container);

        self::assertFalse($container->hasDefinition(RequestDecoratorProcessor::class));
    }

    public function testLoadRegistersGoogleCloudTraceDecoratorWhenEnabled(): void
    {
        $container = new ContainerBuilder();

        $extension = new HttplugLoggerBundleExtension();
        $extension->load([
            ['features' => ['request_decorator' => true, 'gcp_trace_decorator' => true]],
        ], $container);

        self::assertTrue($container->hasDefinition(GoogleCloudTraceDecoratorProcessor::class));
    }

    public function testLoadRegistersPluginServices(): void
    {
        $container = new ContainerBuilder();
        $container->setDefinition('httplug_logger.parser_collection.default', new Definition());
        $container->setDefinition('httplug_logger.level_picker.default', new Definition());
        $container->setDefinition('logger.test', new Definition());

        $extension = new HttplugLoggerBundleExtension();
        $extension->load([
            ['plugins' => ['client' => ['logger' => 'logger.test']]],
        ], $container);

        $definition = $container->getDefinition('httplug_logger.plugin.client');

        self::assertSame('Solitus0\\HttplugLoggerBundle\\LoggerPluginFactory', $definition->getClass());
        self::assertSame(['Solitus0\\HttplugLoggerBundle\\LoggerPluginFactory', 'build'], $definition->getFactory());
    }
}
