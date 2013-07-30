<?php

namespace Da\OAuthServerBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;
use Acme\HelloBundle\DependencyInjection\UnconventionalExtensionClass;

class DaOAuthServerBundle extends Bundle
{
	public function build(ContainerBuilder $container)
    {
        parent::build($container);

        // register extensions that do not follow the conventions manually
        $container->registerExtension(new UnconventionalExtensionClass());
    }
}
