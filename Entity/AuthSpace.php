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
use Da\AuthCommonBundle\Model\AuthSpaceInterface;

/**
 * An AuthSpace is an authspace of definition for a user and a client.
 *
 * @ORM\Entity
 * @ORM\Table(name="AuthSpace", uniqueConstraints={@ORM\UniqueConstraint(name="code_idx", columns={"code"})})
 */
class AuthSpace implements AuthSpaceInterface
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     */
    protected $id;

    /** @ORM\Column(type="string") */  
    protected $code;

    /** @ORM\Column(type="string") */  
    protected $name;

    /**
     * To string.
     * 
     * @return string
     */
    public function __toString() 
    {
        return sprintf('%s',
            $this->getName()
        );
    }

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
     * @return AuthSpace This.
     */
    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * {@inheritdoc}
     */
    public function setCode($code)
    {
        $this->code = $code;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * {@inheritdoc}
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }
}