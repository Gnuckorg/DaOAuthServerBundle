<?php

namespace Da\OAuthServerBundle\Controller;

use Symfony\Component\DependencyInjection\ContainerAware;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use FOS\UserBundle\Controller\SecurityController as BaseSecurityController;
use Da\OAuthServerBundle\Security\AuthSpaceEmailUserProvider;

class SecurityController extends BaseSecurityController
{
	/**
     * Login for an authspace.
     *
     * @Route("/login/{authspace}")
     */
    public function loginAction(Request $request)
    {
    	if ($this->container->has('provider'))
    	{
    		$userProvider = $this->container->find('provider');
    		if ($userProvider instanceof AuthSpaceEmailUserProvider)
    			$userProvider->setAuthSpace($request->attributes->get('authspace'));
    	}

    	return parent::loginAction($request);
    }

    /**
     * {@inheritDoc}
     */
    protected function renderLogin(array $data)
    {
        $data['authspace'] = $this->container->get('request')->attributes->get('authspace');
        $data['check_route'] = 'login_check_'.$data['authspace'];

        $template = sprintf('DaOAuthServerBundle:Security:login.html.%s', $this->container->getParameter('fos_user.template.engine'));

        return $this->container->get('templating')->renderResponse($template, $data);
    }

    /** 
     * @Route("/logout_redirect")
     * Route("/logout_redirect", name="fos_user_security_logout")
     */
    public function logoutRedirectAction()
    {
        $redirectUri = $this->container->get('session')->get('logout_redirect_uri', null);
        return new RedirectResponse($redirectUri, 302);
    }
}
