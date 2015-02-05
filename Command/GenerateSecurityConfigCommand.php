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

        $output->writeln('<comment>File created.</comment>');
        $output->writeln(<<<EOT

<comment>------------------------------------------------
If this has not been done yet, add the following
lines in your config.yml:</comment>

imports:
    - { resource: $relativePath }
<comment>------------------------------------------------</comment>


EOT
        );
    }
}
