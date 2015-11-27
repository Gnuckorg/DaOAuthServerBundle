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
 * @ORM\HasLifecycleCallbacks()
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
     * @var \DateTime
     *
     * @ORM\Column(name="created_at", type="datetime", nullable=true)
     */
    private $createdAt;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="updated_at", type="datetime", nullable=true)
     */
    private $updatedAt;

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
     * @ORM\PrePersist()
     */
    public function onCreate()
    {
        $now = new \DateTime("now");
        $this
            ->setCreatedAt($now)
            ->setUpdatedAt($now)
        ;
    }

    /**
     * @ORM\PreUpdate()
     */
    public function onUpdate()
    {
        $this->setUpdatedAt(new \DateTime("now"));
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
        return json_decode($this->raw, true);
    }

    /**
     * {@inheritdoc}
     */
    public function setRawData(array $raw)
    {
        $this->raw = json_encode($raw, JSON_UNESCAPED_UNICODE);

        return $this;
    }

    /**
     * Get createdAt
     *
     * @return \DateTime
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * Set createdAt
     *
     * @param \DateTime $createdAt
     * @return Offer
     */
    public function setCreatedAt(\DateTime $createdAt = null)
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    /**
     * Get updatedAt
     *
     * @return \DateTime
     */
    public function getUpdatedAt()
    {
        return $this->updatedAt;
    }

    /**
     * Set updatedAt
     *
     * @param \DateTime $updatedAt
     * @return Offer
     */
    public function setUpdatedAt(\DateTime $updatedAt = null)
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }
}