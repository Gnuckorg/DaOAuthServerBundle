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

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;

/**
 * Listen to an existing locale or set a session locale.
 */
class LocaleListener
{
    private $defaultLocale;

    public function __construct($defaultLocale)
    {
        $this->defaultLocale = $defaultLocale;
    }

    public function onKernelRequest(GetResponseEvent $event)
    {
        /*if (HttpKernelInterface::MASTER_REQUEST !== $event->getRequestType()) {
            return;
        }*/

        $request = $event->getRequest();
        // Reset the default locale to have a real getLocale().
        $request->setDefaultLocale(null);

        // Check if a locale has been given to the request.
        if (!$request->getLocale()) {
            $session = $request->getSession();

            // Try the session locale.
            if ($session) {
                $locale = $session->get('_locale', null);
            }

            // Take the best score of the "Accept-Language" header.
            if (!isset($locale)) {
                $locale = $request->getPreferredLanguage();
            }

            $request->setLocale($locale);
        }

        // Set the default local again.
        $request->setDefaultLocale($this->defaultLocale);
    }
}