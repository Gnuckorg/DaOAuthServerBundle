<?php

namespace Da\OAuthServerBundle\Controller;

use Symfony\Component\DependencyInjection\ContainerAware;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use FOS\OAuthServerBundle\Controller\AuthorizeController as BaseAuthorizeController;

/**
 * Controller handling authorization with authspaces.
 *
 * @author Thomas Prelot
 */
class AuthorizeController extends BaseAuthorizeController
{
    /**
     * Authorize
     *
     * @Route("/oauth/v2/auth", name="fos_oauth_server_authorize")
     * @Method({"GET", "POST"})
     */
    public function authorizeAction(Request $request)
    {
        $client = $this->getClient();

        if (null !== $request->request->get('accepted', null) || null !== $request->request->get('rejected', null)) {
            $request->attributes->add(array('authspace' => $client->getAuthSpace()->getCode()));
            return parent::authorizeAction($request);
        }

        $parameters = '?';
        foreach (array_merge($request->query->all(), $request->request->all()) as $name => $value)
        {
            if (is_array($value)) {
                foreach ($value as $subName => $subValue) {
                    $parameters .= $name.'['.$subName.']='.$subValue.'&';
                }
            } else {
                $parameters .= $name.'='.$value.'&';
            }
        }

        return new RedirectResponse($this->container->get('router')->generate('da_oauthserver_authorize_authorizeauthspace', array('authspace' => $client->getAuthSpace()->getCode())).$parameters, 302);
    }

    /**
     * Authorize for an authspace.
     *
     * @Route("/oauth/v2/auth/{authspace}")
     * @Method({"GET", "POST"})
     */
    public function authorizeAuthSpaceAction(Request $request)
    {
        return parent::authorizeAction($request);
    }

    /**
     * Disconnect
     *
     * @Route("/oauth/v2/disconnect")
     * @Method({"GET"})
     */
    public function disconnectAction(Request $request)
    {
        $client = $this->getClient();

        $parameters = '?';
        foreach (array_merge($request->query->all(), $request->request->all()) as $name => $value) {
            if (is_array($value)) {
                foreach ($value as $subName => $subValue) {
                    $parameters .= $name.'['.$subName.']='.$subValue.'&';
                }
            } else {
                $parameters .= $name.'='.$value.'&';
            }
        }

        return new RedirectResponse($this->container->get('router')->generate('da_oauthserver_authorize_disconnectauthspace', array('authspace' => $client->getAuthSpace()->getCode())).$parameters, 302);
    }

    /**
     * Disconnect for an authspace.
     *
     * @Route("/oauth/v2/auth/{authspace}/disconnect")
     * @Method({"GET"})
     * @Template()
     */
    public function disconnectAuthSpaceAction(Request $request, $authspace)
    {    
        $authspaceCode = $authspace;
        $redirectUri = $request->query->get('redirect_uri');
        $logoutUri = $this->container->get('router')->generate('da_oauthserver_authorize_logout', array('authspace' => $authspace)).'?redirect_uri='.urlencode($redirectUri);

        $authspace = $this->container->get('da_oauth_server.authspace_manager')->findAuthSpaceBy(array('code' => $authspaceCode));
        $authspaceName = $this->container->get('translator')->trans($authspace->getName());

        return array('redirectUri' => $redirectUri, 'logoutUri' => $logoutUri, 'authspace' => $authspaceName);
    }

    /**
     * Logout.
     *
     * @Route("/oauth/v2/logout/{authspace}")
     * @Method({"GET"})
     */
    public function logoutAction(Request $request, $authspace)
    {
        $redirectUri = $request->query->get('redirect_uri', null);

        if ($redirectUri) {
            $this->container->get('session')->set('logout_redirect_uri', $redirectUri);
        } else {
            $this->container->get('session')->remove('logout_redirect_uri');
        }
        
        return new RedirectResponse($this->container->get('router')->generate('logout_'.$authspace), 302);
    }
}
