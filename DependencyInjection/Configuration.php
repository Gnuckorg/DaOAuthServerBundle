<?php

namespace Da\OAuthServerBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * This is the class that validates and merges configuration from your app/config files
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html#cookbook-bundles-extension-config-class}
 */
class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritDoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('da_oauth_server');

        $rootNode
            ->addDefaultsIfNotSet()
            ->children()
                ->scalarNode('authspace_class')->isRequired()->cannotBeEmpty()->end()
                ->arrayNode('service')
                    ->addDefaultsIfNotSet()
                        ->children()
                            ->scalarNode('authspace_manager')->defaultValue('da_oauth_server.authspace_manager.default')->end()
                        ->end()
                    ->end()
                ->end()
            ->end();
        ;

        return $treeBuilder;
    }
}
