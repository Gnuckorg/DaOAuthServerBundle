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
use Da\AuthCommonBundle\Exception\InvalidAccessTokenException;
use Da\AuthCommonBundle\Model\UserManagerInterface;

/**
 * UserManager is an implementation of a user manager
 * where you retrieve a user from a database with Doctrine.
 *
 * @author Thomas Prelot
 */
class UserManager implements UserManagerInterface
{
    /**
     * The entity manager.
     *
     * @var object
     */
    protected $em;

    /**
     * The user entity class.
     *
     * @var string
     */
    protected $class;

    /**
     * The access token entity class.
     *
     * @var string
     */
    protected $accessTokenClass;

    /**
     * Constructor.
     *
     * @param ManagerRegistry      $registry         Manager registry.
     * @param string               $class            User entity class.
     * @param string               $accessTokenClass Access token entity class.
     * @param string               $managerName      (optional) Name of the entitymanager to use.
     */
    public function __construct(ManagerRegistry $registry, $class, $accessTokenClass, $managerName = null)
    {
        $this->em = $registry->getManager($managerName);
        $this->class = $class;
        $this->accessTokenClass = $accessTokenClass;
    }

    /**
     * {@inheritdoc}
     */
    public function retrieveUserFromAccessToken($accessToken)
    {
        $accessToken = $this->em->getRepository($this->accessTokenClass)->findOneBy(array('token' => $accessToken));
            
        if (null === $accessToken) {
            throw new InvalidAccessTokenException();
        }

        return $accessToken->getUser();
    }
}
