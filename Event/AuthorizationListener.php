<?php

/**
 * This file is part of the Da Project.
 *
 * (c) Thomas Prelot <tprelot@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Da\OAuthServerBundle\Event;

use FOS\OAuthServerBundle\Event\OAuthEvent;

/**
 * Listen to the oauth authorization events.
 *
 * @author Thomas Prelot <tprelot@gmail.com>
 */
class AuthorizationListener
{
    public function onPreAuthorizationProcess(OAuthEvent $event)
    {
        $client = $event->getClient();

        $event->setAuthorizedClient($client->isTrusted());
    }

    public function onPostAuthorizationProcess(OAuthEvent $event)
    {
    }
}