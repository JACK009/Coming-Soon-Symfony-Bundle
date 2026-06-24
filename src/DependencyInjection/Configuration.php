<?php

declare(strict_types=1);

namespace Jack009\ComingSoonBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * Configuration for the ComingSoonBundle.
 *
 * Example configuration (config/packages/coming_soon.yaml):
 *
 *   coming_soon:
 *       enabled: true
 *       template: '@ComingSoon/coming_soon.html.twig'
 *       status_code: 503
 *       whitelisted_ips: []
 *       excluded_routes: []
 *       excluded_paths: []
 */
class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('coming_soon');
        $rootNode = $treeBuilder->getRootNode();

        $rootNode
            ->children()
                ->booleanNode('enabled')
                    ->defaultFalse()
                    ->info('Set to true to show the Coming Soon page to all visitors.')
                ->end()
                ->scalarNode('template')
                    ->defaultValue('@ComingSoonBundle/coming_soon.html.twig')
                    ->info('Twig template to render for the Coming Soon page.')
                ->end()
                ->integerNode('status_code')
                    ->defaultValue(503)
                    ->min(100)
                    ->max(599)
                    ->info('HTTP status code returned with the Coming Soon page (default: 503).')
                ->end()
                ->arrayNode('whitelisted_ips')
                    ->scalarPrototype()->end()
                    ->defaultValue([])
                    ->info('List of IP addresses that bypass the Coming Soon page.')
                ->end()
                ->arrayNode('excluded_routes')
                    ->scalarPrototype()->end()
                    ->defaultValue([])
                    ->info('List of route names that are excluded from the Coming Soon page.')
                ->end()
                ->arrayNode('excluded_paths')
                    ->scalarPrototype()->end()
                    ->defaultValue([])
                    ->info('List of URL path prefixes excluded from the Coming Soon page (e.g. /admin).')
                ->end()
            ->end()
        ;

        return $treeBuilder;
    }
}
