<?php

namespace Da\OAuthServerBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;
use Symfony\Component\HttpKernel\Kernel;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Da\OAuthServerBundle\DependencyInjection\DaOAuthServerExtension;
use Da\OAuthServerBundle\DependencyInjection\Security\Factory\FormLoginFactory;

class DaOAuthServerBundle extends Bundle
{
    public function __construct()
    {
        $this->extension = new DaOAuthServerExtension();
    }

    public function build(ContainerBuilder $container)
    {
        parent::build($container);

        if (version_compare(Kernel::VERSION, '2.1', '>=')) {
            $extension = $container->getExtension('security');
            $extension->addSecurityListenerFactory(new FormLoginFactory());
        }
    }
}
