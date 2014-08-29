<?php

namespace Da\OAuthServerBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Security\Core\Authentication\Token\AnonymousToken;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
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

            if ($userProvider instanceof AuthSpaceEmailUserProvider) {
                $userProvider->setAuthSpace($request->attributes->get('authspace'));
            }
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
        $data['csrf_token'] = false;

        $template = sprintf('DaOAuthServerBundle:Security:login.html.%s', $this->container->getParameter('fos_user.template.engine'));

        return $this->container->get('templating')->renderResponse($template, $data);
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
        $redirectUri = $request->query->get('redirect_uri');
        $token = $this->container->get('security.context')->getToken();

        if ($token instanceof AnonymousToken) {
            return new RedirectResponse($redirectUri, 302);
        }

        $authspaceCode = $authspace;
        $logoutUri = sprintf(
            '%s?redirect_uri=%s',
            $this->container->get('router')->generate(
                'da_oauthserver_security_logoutauthspace',
                array('authspace' => $authspace)
            ),
            urlencode($redirectUri)
        );

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
    public function logoutAuthSpaceAction(Request $request, $authspace)
    {
        $redirectUri = $request->query->get('redirect_uri', null);

        if ($redirectUri) {
            $this->container->get('session')->set('logout_redirect_uri', $redirectUri);
        } else {
            $this->container->get('session')->remove('logout_redirect_uri');
        }

        $this->container->get('session')->remove('_security_oauth');

        return new RedirectResponse($this->container->get('router')->generate('logout_'.$authspace), 302);
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
