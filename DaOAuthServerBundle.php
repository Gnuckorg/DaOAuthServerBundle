<?php

namespace Da\OAuthServerBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;
use Da\OAuthServerBundle\DependencyInjection\DaOAuthServerExtension;

class DaOAuthServerBundle extends Bundle
{
    public function __construct()
    {
        $this->extension = new DaOAuthServerExtension();
    }
}
