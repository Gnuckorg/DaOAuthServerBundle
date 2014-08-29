<?php

namespace Da\OAuthServerBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;
use Symfony\Component\HttpKernel\Kernel;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Doctrine\Bundle\DoctrineBundle\DependencyInjection\Compiler\DoctrineOrmMappingsPass;
use FOS\UserBundle\DependencyInjection\Compiler\RegisterMappingsPass;
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

        $this->addRegisterMappingsPass($container);
    }

    /**
     * @param ContainerBuilder $container
     */
    private function addRegisterMappingsPass(ContainerBuilder $container)
    {
        // the base class is only available since symfony 2.3
        $symfonyVersion = class_exists('Symfony\Bridge\Doctrine\DependencyInjection\CompilerPass\RegisterMappingsPass');

        $mappings = array(
            realpath(__DIR__ . '/Resources/config/doctrine/model') => 'FOS\UserBundle\Model',
        );

        if ($symfonyVersion && class_exists('Doctrine\Bundle\DoctrineBundle\DependencyInjection\Compiler\DoctrineOrmMappingsPass')) {
            $container->addCompilerPass(DoctrineOrmMappingsPass::createXmlMappingDriver($mappings, array('fos_user.model_manager_name'), 'fos_user.backend_type_orm'));
        } else {
            $container->addCompilerPass(RegisterMappingsPass::createOrmMappingDriver($mappings));
        }
    }
}
