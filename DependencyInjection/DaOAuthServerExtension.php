<?php

namespace Da\OAuthServerBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader;

/**
 * This is the class that loads and manages your bundle configuration
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html}
 */
class DaOAuthServerExtension extends Extension
{
    /**
     * {@inheritDoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.yml');

        $container->setParameter('da_oauth_server.authspace.class', $config['authspace_class']);
        $container->setAlias('da_oauth_server.authspace_manager', $config['service']['authspace_manager']);
        $container->setAlias('da_oauth_server.user_link_manager', $config['service']['user_link_manager']);
    }

    /**
     * {@inheritdoc}
     */
    public function getAlias()
    {
        return 'da_oauth_server';
    }
}
