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
use Tms\Bundle\LoggerBundle\Logger\LoggableInterface;

/**
 * @ORM\Entity
 * @ORM\HasLifecycleCallbacks()
 * @ORM\Table(
 *     name="user",
 *     uniqueConstraints={
 *         @ORM\UniqueConstraint(name="username_authspace", columns={"username_canonical", "auth_space_id"}),
 *         @ORM\UniqueConstraint(name="email_authspace", columns={"email_canonical", "auth_space_id"})
 *     }
 * )
 */
class User extends BaseUser implements LoggableInterface, UserInterface
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
    protected $links;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="created_at", type="datetime", nullable=true)
     */
    protected $createdAt;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="updated_at", type="datetime", nullable=true)
     */
    protected $updatedAt;

    /**
     * @ORM\Column(name="username", type="string", length=255)
     */
    protected $username;

    /**
     * @ORM\Column(name="username_canonical", type="string", length=255)
     */
    protected $usernameCanonical;

    /**
     * @ORM\Column(name="email", type="string", length=255)
     */
    protected $email;

    /**
     * @ORM\Column(name="email_canonical", type="string", length=255)
     */
    protected $emailCanonical;

    /**
     * @ORM\Column(name="enabled", type="boolean")
     */
    protected $enabled;

    /**
     * @ORM\Column(name="salt", type="string")
     */
    protected $salt;

    /**
     * @ORM\Column(name="password", type="string")
     */
    protected $password;

    /**
     * @ORM\Column(name="last_login", type="datetime", nullable=true)
     */
    protected $lastLogin;

    /**
     * @ORM\Column(name="locked", type="boolean")
     */
    protected $locked;

    /**
     * @ORM\Column(name="expired", type="boolean")
     */
    protected $expired;

    /**
     * @ORM\Column(name="expires_at", type="datetime", nullable=true)
     */
    protected $expiresAt;

    /**
     * @ORM\Column(name="confirmation_token", type="string", nullable=true)
     */
    protected $confirmationToken;

    /**
     * @ORM\Column(name="password_requested_at", type="datetime", nullable=true)
     */
    protected $passwordRequestedAt;

    /**
     * @ORM\Column(name="roles", type="array")
     */
    protected $roles;

    /**
     * @ORM\Column(name="credentials_expired", type="boolean")
     */
    protected $credentialsExpired;

    /**
     * @ORM\Column(name="credentials_expire_at", type="datetime", nullable=true)
     */
    protected $credentialsExpireAt;

    /**
     * Constructor
     */
    public function __construct()
    {
        parent::__construct();
    }

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
