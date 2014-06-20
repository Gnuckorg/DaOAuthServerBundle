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

use Symfony\Component\HttpFoundation\Request;

/**
 * The interface that a class should extend to be used
 * as a provider of a request.
 *
 * @author Thomas Prelot <tprelot@gmail.com>
 */
interface RequestProviderInterface
{
    /**
     * Get the request.
     *
     * @return Request The request.
     */
    public function get();

    /**
     * Set the request.
     *
     * @param Request $request The request.
     */
    public function set(Request $request);

    /**
     * Reset the request.
     */
    public function reset();
}
