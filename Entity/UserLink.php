<?php

/**
 * This file is part of the Da Project.
 *
 * (c) Thomas Prelot <tprelot@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Da\OAuthServerBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * A UserLink is a link to an authentication system for a user (facebook, google+, ...).
 *
 * @ORM\Entity
 * @ORM\Table(
 *     name="user_link",
 *     uniqueConstraints={
 *         @ORM\UniqueConstraint(name="user_key", columns={"user", "`key`"}),
 *         @ORM\UniqueConstraint(name="key_value", columns={"`key`", "value"})
 *     }
 * )
 */
class UserLink
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\ManyToOne(targetEntity="Da\OAuthServerBundle\Entity\User", inversedBy="links")
     * @ORM\JoinColumn(name="user", nullable=false)
     */
    protected $user;

    /** @ORM\Column(name="`key`", type="string", nullable=false) */
    protected $key;

    /** @ORM\Column(type="string", nullable=false) */
    protected $value;

    /**
     * Get the id of the authspace.
     *
     * @return integer The id.
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set the id of the authspace.
     *
     * @param integer $id The id.
     *
     * @return UserLink This.
     */
    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }

    /**
     * Get the user of the authspace.
     *
     * @return User The user.
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * Set the user of the authspace.
     *
     * @param User $user The user.
     *
     * @return UserLink This.
     */
    public function setUser(User $user)
    {
        $this->user = $user;

        return $this;
    }

    /**
     * Get the key of the authspace.
     *
     * @return string The key.
     */
    public function getKey()
    {
        return $this->key;
    }

    /**
     * Set the key of the authspace.
     *
     * @param string $key The key.
     *
     * @return UserLink This.
     */
    public function setKey($key)
    {
        $this->key = $key;

        return $this;
    }

    /**
     * Get the value of the authspace.
     *
     * @return string The value.
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * Set the value of the authspace.
     *
     * @param string $value The value.
     *
     * @return UserLink This.
     */
    public function setValue($value)
    {
        $this->value = $value;

        return $this;
    }
}
