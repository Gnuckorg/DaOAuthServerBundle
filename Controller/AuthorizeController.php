<?php

namespace Da\OAuthServerBundle\Controller;

use Symfony\Component\DependencyInjection\ContainerAware;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\Authentication\Token\AnonymousToken;
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
         
            if (null === $token || $token instanceof AnonymousToken) {
                // Forward of the client login form.
                if ($request->query->get('_username')) {
                    $queryParameters = $request->query->all();

                    $router = $this->container->get('router');
                    $route = $router->getRouteCollection()->get(sprintf('login_check_%s', $authspace));
                    $controller = $route->getDefault('_controller');

                    $path = array(
                        '_controller'  => $controller,
                        '_username'    => $queryParameters['_username'],
                        '_password'    => $queryParameters['_password'],
                        '_remember_me' => isset($queryParameters['_remember_me']) ? 1 : 0,
                        '_csrf_token'  => $queryParameters['_csrf_token'],
                    );
                    $request = $this->container->get('request');
                    $subRequest = $request->duplicate(array(), null, $path);

                    $httpKernel = $this->container->get('http_kernel');
                    $response = $httpKernel->handle(
                        $subRequest,
                        HttpKernelInterface::SUB_REQUEST
                    );
                    /*<form action="{{ path("fos_user_security_check") }}" method="post">
                        <input type="hidden" name="_csrf_token" value="{{ csrf_token }}" />

                        <label for="username">{{ 'security.login.username'|trans }}</label>
                        <input type="text" id="username" name="_username" value="{{ last_username }}" required="required" />

                        <label for="password">{{ 'security.login.password'|trans }}</label>
                        <input type="password" id="password" name="_password" required="required" />

                        <input type="checkbox" id="remember_me" name="_remember_me" value="on" />
                        <label for="remember_me">{{ 'security.login.remember_me'|trans }}</label>

                        <input type="submit" id="_submit" name="_submit" value="{{ 'security.login.submit'|trans }}" />
                    </form>*/
                // Redirect to the client login page.
                } else {
                    $csrfToken = $this->container->has('form.csrf_provider')
                        ? $this->container->get('form.csrf_provider')->generateCsrfToken('authenticate')
                        : null
                    ;

                    $redirectUri = explode(
                        '/',
                        $request->query->get('redirect_uri')
                    );
                    array_splice($redirectUri, 3);
                    $redirectDomain = implode(
                        '/',
                        $redirectUri
                    );

                    return new RedirectResponse(
                        sprintf(
                            '%s%s?%s',
                            $redirectDomain,
                            $clientLoginPath,
                            sprintf('_csrf_token=%s', $csrfToken)
                        ),
                        302
                    );
                }
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

        return new RedirectResponse(
            sprintf(
                '%s?%s',
                $this->container->get('router')->generate(
                    'da_oauthserver_security_disconnectauthspace',
                    array('authspace' => $client->getAuthSpace()->getCode())
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

        foreach (array_merge($request->query->all(), $request->request->all()) as $name => $value) {
            if (is_array($value)) {
                foreach ($value as $subName => $subValue) {
                    $queryString .= sprintf('%s[%s]=%s&', $name, $subName, $subValue);
                }
            } else {
                $queryString .= sprintf('%s=%s&', $name, $value);
            }
        }

        return $queryString;
    }
}
