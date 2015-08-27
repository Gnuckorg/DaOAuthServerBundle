<?php

namespace Da\OAuthServerBundle\Security\Authentication\Token;

use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken as BaseUsernamePasswordToken;
use Symfony\Component\Security\Core\Role\RoleInterface;

/**
 * UsernamePasswordToken implements a username and password token.
 *
 * @author Thomas Prelot <tprelot@gmail.com>
 */
class UsernamePasswordToken extends BaseUsernamePasswordToken
{
    private $linkKey;

    /**
     * Constructor.
     *
     * @param string|object            $user        The username (like a nickname, email address, etc.), or a UserInterface instance or an object implementing a __toString method.
     * @param string                   $credentials This usually is the password of the user
     * @param string                   $linkKey     The link key
     * @param string                   $providerKey The provider key
     * @param RoleInterface[]|string[] $roles       An array of roles
     *
     * @throws \InvalidArgumentException
     */
    public function __construct($user, $credentials, $linkKey, $providerKey, array $roles = array())
    {
        $this->linkKey = $linkKey;

        parent::__construct($user, $credentials, $providerKey, $roles);
    }

    /**
     * Returns the link key.
     *
     * @return string The provider key
     */
    public function getLinkKey()
    {
        return $this->linkKey;
    }

    /**
     * {@inheritdoc}
     */
    public function serialize()
    {
        return serialize(array($this->linkKey, parent::serialize()));
    }

    /**
     * {@inheritdoc}
     */
    public function unserialize($serialized)
    {
        list($this->linkKey, $parentStr) = unserialize($serialized);
        parent::unserialize($parentStr);
    }
}
