<?php

declare(strict_types=1);

namespace Solitus0\HttplugLoggerBundle\DependencyInjection;

use Solitus0\HttplugLoggerBundle\LoggerPluginFactory;
use Solitus0\HttplugLoggerBundle\Monolog\Processor\GoogleCloudTraceDecoratorProcessor;
use Solitus0\HttplugLoggerBundle\Monolog\Processor\RequestDecoratorProcessor;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Reference;

class HttplugLoggerBundleExtension extends Extension
{
    public function load(array $configs, ContainerBuilder $container): void
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $features = $config['features'];
        if (!$features['request_decorator'] && $container->hasDefinition(RequestDecoratorProcessor::class)) {
            $container->removeDefinition(RequestDecoratorProcessor::class);
        }

        if ($features['gcp_trace_decorator']) {
            $container->setDefinition(
                GoogleCloudTraceDecoratorProcessor::class,
                (new Definition(GoogleCloudTraceDecoratorProcessor::class))
                    ->setAutowired(true)
                    ->setAutoconfigured(true)
                    ->setArguments(['$googleCloudProject' => '%env(GOOGLE_CLOUD_PROJECT_ID)%'])
            );
        } else {
            $container->removeDefinition(GoogleCloudTraceDecoratorProcessor::class);
        }

        foreach ($config['plugins'] as $instanceName => $instanceConfig) {
            $serviceId = 'httplug_logger.plugin.' . $instanceName;

            $definition = $container->register($serviceId, LoggerPluginFactory::class)
                ->setFactory([LoggerPluginFactory::class, 'build'])
                ->setArguments([
                    new Reference($instanceConfig['parser_collection']),
                    new Reference($instanceConfig['level_picker']),
                    new Reference($instanceConfig['logger']),
                    $instanceName,
                ])
            ;

            $container->setDefinition($serviceId, $definition);
        }

        $parserConfig = $config['parser'];
        $container->setParameter('httplug_logger.parser.response_payload_truncate', $parserConfig['response_payload_truncate']);
        $container->setParameter('httplug_logger.parser.response_payload_max_kilobyte_size', $parserConfig['response_payload_max_kilobyte_size']);
    }

    public function getAlias(): string
    {
        return 'httplug_logger';
    }
}
