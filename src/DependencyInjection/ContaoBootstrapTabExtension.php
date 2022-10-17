<?php

declare(strict_types=1);

namespace ContaoBootstrap\Tab\DependencyInjection;

use ContaoBootstrap\Tab\Component\ContentElement\TabElementFactory;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use Symfony\Component\DependencyInjection\Reference;

use function dirname;

final class ContaoBootstrapTabExtension extends Extension
{
    /**
     * {@inheritdoc}
     */
    public function load(array $configs, ContainerBuilder $container): void
    {
        $loader = new XmlFileLoader(
            $container,
            new FileLocator(dirname(__DIR__) . '/Resources/config')
        );

        $loader->load('services.xml');
        $loader->load('listener.xml');

        $this->configureTabElementFactory($container);
    }

    /**
     * Configure the tab element factory.
     *
     * @param ContainerBuilder $container The container builder.
     */
    private function configureTabElementFactory(ContainerBuilder $container): void
    {
        $definition = $container->findDefinition(TabElementFactory::class);
        if (! $definition) {
            return;
        }

        $bundles = $container->getParameter('kernel.bundles');

        if (! isset($bundles['ContaoBootstrapGridBundle'])) {
            return;
        }

        $definition->setArgument(5, new Reference('contao_bootstrap.grid.grid_provider'));
    }
}
