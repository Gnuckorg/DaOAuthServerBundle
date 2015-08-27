<?php

/*
 * This file is part of the Da Project.
 *
 * (c) Thomas Prelot <tprelot@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Da\OAuthServerBundle\DependencyInjection\Security\Factory;

use Symfony\Component\DependencyInjection\DefinitionDecorator;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Bundle\SecurityBundle\DependencyInjection\Security\Factory\FormLoginFactory as BaseFactory;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * @author Thomas Prelot <tprelot@gmail.com>
 */
class FormLoginFactory extends BaseFactory
{
    public function __construct()
    {
        parent::__construct();

        $this->addOption('link_parameter', '_link');
    }

    public function getKey()
    {
        return 'da-oauth-form-login';
    }

    protected function getListenerId()
    {
        return 'da_oauth_server.security.authentication.listener.form';
    }

    protected function createAuthProvider(ContainerBuilder $container, $id, $config, $userProviderId)
    {
        $provider = 'security.authentication.provider.da_oauth_server.dao.'.$id;
        $container
            ->setDefinition($provider, new DefinitionDecorator('da_oauth_server.security.authentication.provider.dao'))
            ->replaceArgument(0, new Reference($userProviderId))
            ->replaceArgument(2, $id)
        ;

        return $provider;
    }

    protected function createEntryPoint($container, $id, $config, $defaultEntryPoint)
    {
        $entryPointId = 'security.authentication.entry_point.da_oauth_server.form.'.$id;
        $container
            ->setDefinition($entryPointId, new DefinitionDecorator('da_oauth_server.security.entry_point.form'))
            ->addArgument(new Reference('security.http_utils'))
            ->addArgument($config['login_path'])
            ->addArgument($config['use_forward'])
        ;

        return $entryPointId;
    }
}
