<?php

namespace Da\OAuthServerBundle\Controller;

use Symfony\Component\DependencyInjection\ContainerAware;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
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

        return new RedirectResponse($this->container->get('router')->generate('da_oauthserver_security_disconnectauthspace', array('authspace' => $client->getAuthSpace()->getCode())).$parameters, 302);
    }
}
