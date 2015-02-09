<?php

namespace Da\OAuthServerBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Filesystem;

class GenerateSecurityConfigCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('da:oauth-server:generate:security-config')
            ->setDescription('Generate the security config file for all configured authspaces')
            ->setHelp(<<<EOT
The <info>%command.name%</info> command allow to generate the security config file for all configured authspaces (by default in the file app/config/security-daoauthserver.yml).
Here is an example of usage of this command with a defined path:

<info>php app/console %command.name% -p app/config/security-oauth.yml</info>:
EOT
            )
            ->addOption('path', 'p', InputOption::VALUE_OPTIONAL)
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $path = $input->getOption('path');
        $container = $this->getContainer();
        $fs = new Filesystem();
        $rootPath = $container->getParameter('kernel.root_dir');
        $fullPath = $rootPath;

        if ($path) {
            $path = str_replace('\\', '/', $path);

            if ('/' !== substr($path, 0, 1)) {
                $path = '/'.$path;
            }

            if ('.yml' !== substr($path, -4)) {
                if ('/' !== substr($path, -1)) {
                    $path .= '/';
                }
                $path .= 'security-daoauthserver.yml';
            }
        } else {
            $path = '/config/security-daoauthserver.yml';
        }

        $fullPath .= $path;
        $directories = explode('/', $fullPath);
        $filename = array_pop($directories);
        $directory = implode('/', $directories);
        $relativePath = $fs->makePathRelative(
            $directory,
            $rootPath.'/config'
        );

        $output->writeln(implode('/', $directories));
        $output->writeln($fullPath.'/config');

        if (empty($relativePath) || $relativePath === './') {
            $relativePath = $filename;
        } else {
            $relativePath .= $filename;
        }

        if (!$fs->exists($directory)) {
            $fs->mkdir($directory, 0755);
        }

        if ($fs->exists($fullPath)) {
            $fs->remove($fullPath);
        }

        $output->writeln(sprintf(<<<EOT
<info>Creation of the file "%s"...
...</info>
EOT
            ,
            $fullPath
        ));

        $file = fopen($fullPath, 'a+');
        fwrite($file, <<<EOT
security:
    firewalls:
EOT
        );

        $authspaces = $container
            ->get('da_oauth_server.authspace_manager.default')
            ->findAuthSpacesBy()
        ;

        foreach ($authspaces as $authspace) {
            $code = $authspace->getCode();

            if ('api' !== $code) {
                fwrite($file, <<<EOT

        oauth_authorize_{$code}:
            pattern: ^/oauth/v2/auth/{$code}
            da_oauth_form_login:
                provider:      fos_userbundle
                login_path:    /login/{$code}
                check_path:    /oauth/v2/auth/{$code}/login_check
            logout:
                path:   /oauth/v2/auth/{$code}/logout
                target: /logout_redirect
                # BUG: https://github.com/sensiolabs/SensioDistributionBundle/commit/2a518e7c957b66c9478730ca95f67e16ccdc982b
                invalidate_session: false
            anonymous: ~

EOT
                );
            }
        }

        fwrite($file, <<<EOT

        oauth_authorize_api:
            pattern: (^/oauth/v2/auth/api|^/)
            da_oauth_form_login:
                provider:      fos_userbundle
                login_path:    /login/api
                check_path:    /oauth/v2/auth/api/login_check
                default_target_path: tms_sso
            logout:
                path:   /oauth/v2/auth/api/logout
                target: /logout_redirect
                # BUG: https://github.com/sensiolabs/SensioDistributionBundle/commit/2a518e7c957b66c9478730ca95f67e16ccdc982b
                invalidate_session: false
            anonymous: ~

EOT
            );

        $output->writeln('<comment>File created.</comment>');
        $output->writeln(<<<EOT

<comment>-----------------------------------------------------
If this has not been done yet, replace the following
lines in your config.yml:</comment>

imports:
    - { resource: security.yml }

<comment>with:</comment>

imports:
    - { resource: $relativePath }
<comment>-----------------------------------------------------</comment>


EOT
        );
    }
}
