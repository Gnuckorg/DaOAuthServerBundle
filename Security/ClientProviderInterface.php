<?php

/*
 * This file is part of the Da Project.
 *
 * (c) Thomas Prelot <tprelot@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Da\OAuthServerBundle\Security;

/**
 * ClientProviderInterface is the interface that a class should implement
 * to be used as a client provider.
 *
 * @author Thomas Prelot <tprelot@gmail.com>
 */
interface ClientProviderInterface
{
    /**
     * Return a client.
     *
     * @return ClientInterface The client.
     */
    function getClient();
}
