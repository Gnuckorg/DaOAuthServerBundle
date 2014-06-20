<?php

/**
 * This file is part of the Da Project.
 *
 * (c) Thomas Prelot <tprelot@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Da\OAuthServerBundle\Http;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * A basic implementation of a provider of the current request.
 *
 * @author Thomas Prelot <tprelot@gmail.com>
 */
class RequestProvider implements RequestProviderInterface
{
    /**
     * The current request.
     *
     * @var Request
     */
    protected $request;

    /**
     * Constructor.
     *
     * @param ContainerInterface $container The services container.
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * {@inheritDoc}
     */
    public function get()
    {
        if ($this->request) {
            return $this->request;
        }

        return $this->container->get('request');
    }

    /**
     * {@inheritDoc}
     */
    public function set(Request $request)
    {
        $this->request = $request;
    }

    /**
     * {@inheritDoc}
     */
    public function reset()
    {
        $this->request = null;
    }
}
