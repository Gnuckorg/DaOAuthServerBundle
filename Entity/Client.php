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

use FOS\OAuthServerBundle\Entity\Client as BaseClient;
use FOS\OAuthServerBundle\Util\Random;
use Doctrine\ORM\Mapping as ORM;
use Da\AuthCommonBundle\Model\AuthSpaceInterface;
use Da\AuthCommonBundle\Model\ClientInterface;

/**
 * @ORM\Entity
 */
class Client extends BaseClient implements ClientInterface
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\Column(name="name", type="string")
     */
    protected $name;

    /**
     * @ORM\Column(name="scope", type="string", nullable=true)
     */
    protected $scope;

    /**
     * @ORM\Column(name="api_token", type="string", nullable=false)
     */
    protected $apiToken;

    /**
     * @ORM\ManyToOne(targetEntity="Da\OAuthServerBundle\Entity\AuthSpace")
     * @ORM\JoinColumn(name="auth_space", nullable=false)
     */
    protected $authSpace;

    /**
     * @ORM\Column(name="trusted", type="boolean", nullable=false)
     */
    protected $trusted = false;

    /**
     * @ORM\Column(name="client_login_path", type="string", nullable=true)
     */
    protected $clientLoginPath;

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->setApiToken(Random::generateToken());

        parent::__construct();
    }

    /**
     * To string.
     *
     * @return string
     */
    public function __toString()
    {
        return sprintf('%s %s %s',
            $this->getScope(),
            $this->getName(),
            $this->getAuthSpace()
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getApiToken()
    {
        return $this->apiToken;
    }

    /**
     * {@inheritdoc}
     */
    public function setApiToken($apiToken)
    {
        $this->apiToken = $apiToken;

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

    /**
     * {@inheritdoc}
     */
    public function getScope()
    {
        return $this->scope;
    }

    /**
     * {@inheritdoc}
     */
    public function setScope($scope)
    {
        $this->scope = $scope;

        return $this;
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
    public function isTrusted()
    {
        return $this->trusted;
    }

    /**
     * {@inheritdoc}
     */
    public function setTrusted($trusted)
    {
        $this->trusted = $trusted ? true : false;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getClientLoginPath()
    {
        return $this->clientLoginPath;
    }

    /**
     * {@inheritdoc}
     */
    public function setClientLoginPath($clientLoginPath)
    {
        $this->clientLoginPath = $clientLoginPath;

        return $this;
    }
}