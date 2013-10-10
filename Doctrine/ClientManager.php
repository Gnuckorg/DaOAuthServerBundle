<?php

/**
 * This file is part of the Da Project.
 *
 * (c) Thomas Prelot <tprelot@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Da\OAuthServerBundle\Doctrine;

use Doctrine\Common\Persistence\ManagerRegistry;
use Da\AuthCommonBundle\Exception\InvalidApiTokenException;
use Da\AuthCommonBundle\Model\ClientManagerInterface;

/**
 * ClientManager is an implementation of a client manager
 * where you retrieve a client from a database with Doctrine.
 *
 * @author Thomas Prelot
 */
class ClientManager implements ClientManagerInterface
{
    /**
     * The entity manager.
     *
     * @var object
     */
    protected $em;

    /**
     * The client entity class.
     *
     * @var string
     */
    protected $class;

    /**
     * Constructor.
     *
     * @param ManagerRegistry        $registry      Manager registry.
     * @param string                 $class         Client entity class.
     * @param string                 $managerName   (optional) Name of the entitymanager to use.
     */
    public function __construct(ManagerRegistry $registry, $class, $managerName = null)
    {
        $this->em = $registry->getManager($managerName);
        $this->class = $class;
    }

    /**
     * {@inheritdoc}
     */
    public function retrieveClientFromApiToken($apiToken)
    {
        $client = $this->em->getRepository($this->class)->findOneBy(array('apiToken' => $apiToken));
    
        if (null === $client) {
            throw new InvalidApiTokenException();
        }

        return $client;
    }
}
