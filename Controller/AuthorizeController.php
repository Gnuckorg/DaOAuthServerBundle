<?php

namespace Da\OAuthServerBundle\Controller;

use Symfony\Component\DependencyInjection\ContainerAware;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\SecurityContextInterface;
use Symfony\Component\Security\Core\Authentication\Token\AnonymousToken;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use FOS\OAuthServerBundle\Controller\AuthorizeController as BaseAuthorizeController;

/**
 * Controller handling authorization with authspaces.
 *
 * @author Thomas Prelot <tprelot@gmail.com>
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
        $authspace = $client->getAuthSpace()->getCode();

        if (null !== $request->request->get('accepted', null) || null !== $request->request->get('rejected', null)) {
            $request->attributes->add(array('authspace' => $authspace));
            return parent::authorizeAction($request);
        }

        $clientLoginPath = $client->getClientLoginPath();
        
        if ($client->isTrusted() && $clientLoginPath) {
            $token = $this->container->get('security.context')->getToken();
            $username = $request->request->get('_username', null);
            $authError = '';

            if (null === $token || $token instanceof AnonymousToken) {
                // Forward of the client login form.
                if ($username) {
                    $requestParameters = $request->request->all();

                    $router = $this->container->get('router');
                    $requestUri = $router->generate(sprintf('login_check_%s', $authspace), array(), false);
                    $authRequest = $request->duplicate(
                        array(),
                        array(
                            '_username' => $requestParameters['_username'],
                            '_password' => $requestParameters['_password'],
                            '_remember_me' => isset($requestParameters['_remember_me']) ? 1 : 0,
                            '_csrf_token' => $requestParameters['_csrf_token'],
                            '_authspace' => $authspace
                        ),
                        array(),
                        $request->cookies->all(),
                        array(),
                        array(
                            'REQUEST_METHOD' => 'POST',
                            'REQUEST_URI' => $requestUri
                        )
                    );

                    $httpKernel = $this->container->get('http_kernel');
                    $requestProvider = $this->container->get('da_oauth_server.http.request_provider');
                    $requestProvider->set($authRequest);
                    $event = new GetResponseEvent($httpKernel, $authRequest, HttpKernelInterface::MASTER_REQUEST);
                    $firewall = $this->container->get('security.firewall');
                    $firewall->onKernelRequest($event);
                    $requestProvider->reset();
                    
                    $token = $this->container->get('security.context')->getToken();
                    if (null === $token) {
                        $authError = $request->getSession()->get(SecurityContextInterface::AUTHENTICATION_ERROR)->getMessage();
                    }
                }
            }
                
            // Redirect to the client login page.
            if (null === $username || !empty($authError)) {
                $csrfToken = $this->container->has('form.csrf_provider')
                    ? $this->container->get('form.csrf_provider')->generateCsrfToken('authenticate')
                    : null
                ;

                $redirectUri = $request->query->get('redirect_uri');
                $parsedUri = parse_url($redirectUri);

                return new RedirectResponse(
                    sprintf(
                        '%s://%s%s?%s',
                        $parsedUri['scheme'],
                        $parsedUri['host'],
                        $clientLoginPath,
                        sprintf(
                            'csrf_token=%s&redirect_uri=%s&auth_error=%s',
                            $csrfToken,
                            $redirectUri,
                            $authError
                        )
                    ),
                    302
                );
            }
        }

        return new RedirectResponse(
            sprintf(
                '%s?%s',
                $this->container->get('router')->generate(
                    'da_oauthserver_authorize_authorizeauthspace',
                    array('authspace' => $client->getAuthSpace()->getCode())
                ),
                $this->retrieveQueryString($request)
            ),
            302
        );
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
        $authspace = $client->getAuthSpace()->getCode();
        $redirectUri = $request->query->get('redirect_uri');

        if ($client->isTrusted()) {
            return new RedirectResponse(
                sprintf(
                    '%s?redirect_uri=%s',
                    $this->container->get('router')->generate(
                        'da_oauthserver_security_logoutauthspace',
                        array('authspace' => $authspace)
                    ),
                    urlencode($redirectUri)
                ),
                302
            );
        }

        return new RedirectResponse(
            sprintf(
                '%s?%s',
                $this->container->get('router')->generate(
                    'da_oauthserver_security_disconnectauthspace',
                    array('authspace' => $authspace)
                ),
                $this->retrieveQueryString($request)
            ),
            302
        );
    }

    /**
     * Retrieve the query string of the request.
     *
     * @param Request $request The resquest.
     *
     * @return string The query string.
     */
    public function retrieveQueryString(Request $request)
    {
        $queryString = '';

        $skippedItems = array(
            '_username'    => true,
            '_password'    => true,
            '_remember_me' => true,
            '_csrf_token'  => true,
            '_submit'  => true
        );

        foreach (array_merge($request->query->all(), $request->request->all()) as $name => $value) {
            if (!isset($skippedItems[$name])) {
                if (is_array($value)) {
                    foreach ($value as $subName => $subValue) {
                        $queryString .= sprintf('%s[%s]=%s&', $name, $subName, $subValue);
                    }
                } else {
                    $queryString .= sprintf('%s=%s&', $name, $value);
                }
            }
        }

        return $queryString;
    }
}
