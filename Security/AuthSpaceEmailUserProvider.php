<?php

namespace Da\OAuthServerBundle\Security;

use FOS\UserBundle\Security\UserProvider;
use FOS\UserBundle\Model\UserManagerInterface;
use FOS\OAuthServerBundle\Controller\AuthorizeController;
use Da\AuthCommonBundle\Model\AuthSpaceInterface;
use Da\AuthCommonBundle\Model\AuthSpaceManagerInterface;
use Da\OAuthServerBundle\Http\RequestProviderInterface;

class AuthSpaceEmailUserProvider extends UserProvider
{
    /**
     * The authSpace of definition of the user.
     *
     * @var string
     */
    protected $authSpace;

    /**
     * The request provider.
     *
     * @var RequestProviderInterface
     */
    protected $requestProvider;

    /**
     * The authSpace manager.
     *
     * @var AuthSpaceManagerInterface
     */
    protected $authSpaceManager;

    /**
     * Constructor.
     *
     * @param UserManagerInterface      $userManager
     * @param RequestProviderInterface  $requestProvider
     * @param AuthSpaceManagerInterface $authspaceManager
     */
    public function __construct(UserManagerInterface $userManager, RequestProviderInterface $requestProvider, AuthSpaceManagerInterface $authspaceManager)
    {
        $this->userManager = $userManager;
        $this->requestProvider = $requestProvider;
        $this->authspaceManager = $authspaceManager;
    }

    /**
     * Set the authspace of definition of the user.
     *
     * @param AuthSpaceInterface $authSpace The authspace.
     */
    public function setAuthSpace(AuthSpaceInterface $authSpace)
    {
        $this->authSpace = $authSpace;
    }

    /**
     * {@inheritDoc}
     */
    protected function findUser($username)
    {
        if (null === $this->authSpace) {
            $authSpaceCode = $this->requestProvider->get()->request->get('_authspace', null);
            $this->setAuthSpace($this->authspaceManager->findAuthSpaceBy(array('code' => $authSpaceCode)));
        }

        return $this->userManager->findUserBy(array('authSpace' => $this->authSpace, 'email' => $username));
    }
}
