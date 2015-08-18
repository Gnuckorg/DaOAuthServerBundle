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

use FOS\UserBundle\Model\User as BaseUser;
use Doctrine\ORM\Mapping as ORM;
use Da\AuthCommonBundle\Model\AuthSpaceInterface;
use Da\AuthCommonBundle\Model\UserInterface;

/**
 * @ORM\Entity
 * @ORM\Table(
 *     name="User",
 *     uniqueConstraints={
 *         @ORM\UniqueConstraint(name="username_authspace", columns={"username_canonical", "auth_space_id"}),
 *         @ORM\UniqueConstraint(name="email_authspace", columns={"email_canonical", "auth_space_id"})
 *     }
 * )
 */
class User extends BaseUser implements UserInterface
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\ManyToOne(targetEntity="Da\OAuthServerBundle\Entity\AuthSpace")
     * @ORM\JoinColumn(name="auth_space_id", nullable=false)
     */
    protected $authSpace;

    /**
     * @ORM\Column(name="raw", type="text", nullable=true)
     */
    protected $raw = '{}';

    /**
     * @ORM\OneToMany(targetEntity="Da\OAuthServerBundle\Entity\UserLink", mappedBy="user")
     **/
    private $links;

    /**
     * To string.
     *
     * @return string
     */
    public function __toString()
    {
        return sprintf('%s',
            $this->getEmail()
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getAuthSpace()
    {
        return $this->authSpace;
    }

    /**
     * {@inheritdoc}
     */
    public function setAuthSpace(AuthSpaceInterface $authSpace)
    {
        $this->authSpace = $authSpace;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getRaw()
    {
        return $this->raw;
    }

    /**
     * {@inheritdoc}
     */
    public function setRaw($raw)
    {
        $this->raw = $raw;

        return $this;
    }

    /**
     * Get authentication links.
     *
     * @return array<UserLink> The links.
     */
    public function getLinks()
    {
        return $this->links;
    }

    /**
     * {@inheritdoc}
     */
    public function getRawData()
    {
        return (array)json_decode($this->raw);
    }

    /**
     * {@inheritdoc}
     */
    public function setRawData(array $raw)
    {
        $this->raw = json_encode($raw);

        return $this;
    }
}