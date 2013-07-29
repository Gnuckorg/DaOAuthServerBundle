<?php

namespace Da\OAuthServerBundle\Security;

use Symfony\Component\DependencyInjection\ContainerInterface;
use FOS\UserBundle\Security\UserProvider;
use FOS\UserBundle\Model\UserManagerInterface;
use FOS\OAuthServerBundle\Controller\AuthorizeController;
use Da\OAuthServerBundle\Model\AuthSpaceInterface;
use Da\OAuthServerBundle\Model\AuthSpaceManagerInterface;

class AuthSpaceEmailUserProvider extends UserProvider
{
	/**
	 * The authSpace of definition of the user.
	 *
	 * @var string
	 */
	protected $authSpace;

	/**
	 * The container.
	 *
	 * @var ContainerInterface
	 */
	protected $container;

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
     * @param ContainerInterface        $container
     * @param AuthSpaceManagerInterface $authspaceManager
     */
    public function __construct(UserManagerInterface $userManager, ContainerInterface $container, AuthSpaceManagerInterface $authspaceManager)
    {
        $this->userManager = $userManager;
        $this->container = $container;
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
    	if (null === $this->authSpace)
    	{
    		$authSpaceCode = $this->container->get('request')->request->get('_authspace', null);
    		$this->setAuthSpace($this->authspaceManager->findAuthSpaceBy(array('code' => $authSpaceCode)));
    	}

        return $this->userManager->findUserBy(array('authSpace' => $this->authSpace, 'email' => $username));
    }
}
