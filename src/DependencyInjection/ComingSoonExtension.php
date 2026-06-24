<?php

declare(strict_types=1);

namespace Jack009\ComingSoonBundle\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;

class ComingSoonExtension extends Extension
{
    public function load(array $configs, ContainerBuilder $container): void
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $loader = new YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.yaml');

        $container->setParameter('coming_soon.enabled', $config['enabled']);
        $container->setParameter('coming_soon.template', $config['template']);
        $container->setParameter('coming_soon.status_code', $config['status_code']);
        $container->setParameter('coming_soon.whitelisted_ips', $config['whitelisted_ips']);
        $container->setParameter('coming_soon.excluded_routes', $config['excluded_routes']);
        $container->setParameter('coming_soon.excluded_paths', $config['excluded_paths']);
    }

    public function getAlias(): string
    {
        return 'coming_soon';
    }
}
