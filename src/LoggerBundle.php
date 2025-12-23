<?php

declare(strict_types=1);

namespace Solitus0\HttplugLoggerBundle;

use Solitus0\HttplugLoggerBundle\DependencyInjection\HttplugLoggerBundleExtension;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\ExtensionInterface;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class LoggerBundle extends Bundle
{
    public function build(ContainerBuilder $container): void
    {
        parent::build($container);

        $loader = new YamlFileLoader($container, new FileLocator(__DIR__ . '/Resources/config'));
        $loader->load('services.yaml');
    }

    public function getContainerExtension(): ?ExtensionInterface
    {
        return new HttplugLoggerBundleExtension();
    }
}
