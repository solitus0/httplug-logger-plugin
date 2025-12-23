<?php

namespace Solitus0\HttplugLoggerBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('httplug_logger');

        $treeBuilder->getRootNode()
            ->children()
                ->arrayNode('features')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->booleanNode('request_decorator')
                            ->info('Enable adding request metadata to log records')
                            ->defaultTrue()
                        ->end()
                        ->booleanNode('gcp_trace_decorator')
                            ->info('Enable adding Google Cloud trace data to log records')
                            ->defaultFalse()
                        ->end()
                    ->end()
                ->end()
                ->arrayNode('plugins')
                    ->useAttributeAsKey('name')
                    ->arrayPrototype()
                        ->children()
                            ->scalarNode('parser_collection')
                                ->info('Service ID of a PsrHttpMessageParserCollection instance')
                                ->defaultValue('httplug_logger.parser_collection.default')
                            ->end()
                            ->scalarNode('level_picker')
                                ->info('Service ID of a class implementing LogLevelPickerInterface')
                                ->defaultValue('httplug_logger.level_picker.default')
                            ->end()
                            ->scalarNode('logger')
                                ->info('Service ID of a PSR-3 logger implementing LoggerInterface')
                                ->isRequired()
                            ->end()
                        ->end()
                    ->end()
                ->end()
                ->arrayNode('parser')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->booleanNode('response_payload_truncate')
                            ->info('Enable truncating response payload logs when size exceeds the limit')
                            ->defaultTrue()
                        ->end()
                        ->integerNode('response_payload_max_kilobyte_size')
                            ->info('Maximum response payload size (KB) before truncation')
                            ->min(1)
                            ->defaultValue(7)
                        ->end()
                    ->end()
                ->end()
            ->end();

        return $treeBuilder;
    }
}
