<?php

/*
 * This file is part of the Da Project.
 *
 * (c) Thomas Prelot <tprelot@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Da\OAuthServerBundle\Security\EntryPoint;

use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\Security\Http\HttpUtils;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\Security\Core\SecurityContextInterface;
use Symfony\Component\Security\Core\Authentication\Token\AnonymousToken;
use Symfony\Component\Security\Http\EntryPoint\FormAuthenticationEntryPoint as BaseEntryPoint;
use Da\OAuthServerBundle\Security\ClientProviderInterface;

/**
 * FormAuthenticationEntryPoint starts an authentication via a login form.
 *
 * @author Thomas Prelot <tprelot@gmail.com>
 */
class FormAuthenticationEntryPoint extends BaseEntryPoint
{
    /**
     * The kernel.
     *
     * @var HttpKernelInterface
     */
    protected $kernel;

    /**
     * The container.
     *
     * @var ContainerInterface
     */
    protected $container;

    /**
     * The client provider.
     *
     * @var ClientProviderInterface
     */
    protected $clientProvider;

    /**
     * Constructor.
     *
     * @param HttpKernelInterface     $kernel         The kernel.
     * @param ContainerInterface      $container      The container.
     * @param ClientProviderInterface $clientProvider A client provider.
     * @param HttpUtils               $httpUtils      An HttpUtils instance.
     * @param string                  $loginPath      The path to the login form.
     * @param bool                    $useForward     Whether to forward or redirect to the login form.
     */
    public function __construct(
        HttpKernelInterface $kernel,
        ContainerInterface $container,
        ClientProviderInterface $clientProvider,
        HttpUtils $httpUtils,
        $loginPath,
        $useForward = false
    )
    {
        $this->kernel = $kernel;
        $this->container = $container;
        $this->clientProvider = $clientProvider;

        parent::__construct($kernel, $httpUtils, $loginPath, $useForward);
    }

    /**
     * {@inheritdoc}
     */
    public function start(Request $request, AuthenticationException $authException = null)
    {
        try {
            $client = $this->clientProvider->getClient();
        } catch (\Exception $e) {
            $client = null;
        }

        if (null !== $client) {
            $authspace = $client->getAuthSpace()->getCode();
            $clientLoginPath = $client->getClientLoginPath();

            if ($client->isTrusted() && $clientLoginPath) {
                $query = '';
                $requestParameters = array_merge(
                    $request->query->all(),
                    $request->request->all()
                );
                $username = $request->get('_username', null);
                $account = $request->get('account', null);
                $authError = '';

                // Forward the client login form to the SSO for authentication.
                if ($username || $account) {
                    // Handle registration and profile.
                    if ($account) {
                        $router = $this->container->get('router');
                        $formName = $requestParameters['form_name'];
                        $formParameters = $requestParameters[$formName];

                        if (!isset($formParameters['username']) && isset($formParameters['email'])) {
                            $requestParameters[$formName]['username'] = $formParameters['email'];
                        }
                        if (!isset($formParameters['raw'])) {
                            $requestParameters[$formName]['raw'] = array();
                        }
                        /*if (!isset($formParameters['_token'])) {
                            $requestParameters[$formName]['_token'] = $requestParameters['_csrf_token'];
                        }*/

                        $query = http_build_query(array(
                            'form_cached_values' => $requestParameters[$formName]
                        ));

                        switch ($account) {
                            case 'registration':
                                $requestUri = $router->generate(
                                    'da_oauthserver_registration_register',
                                    array('authspace' => $authspace),
                                    false
                                );

                                break;
                            case 'profile':
                                $requestUri = $router->generate(
                                    'da_oauthserver_profile_edit',
                                    array('authspace' => $authspace),
                                    false
                                );

                                break;
                            default:
                                throw new HttpException(400, 'Parameter "account" authorized values are ["registration", "profile"].');
                        }

                        $accountRequest = $request->duplicate(
                            $request->query->all(),
                            $request->request->all(),
                            array(),
                            $request->cookies->all(),
                            array(),
                            array_merge(
                                $request->server->all(),
                                array(
                                    'REQUEST_METHOD' => 'POST',
                                    'REQUEST_URI' => $requestUri
                                )
                            )
                        );

                        $httpKernel = $this->container->get('http_kernel');
                        $response = $httpKernel->handle($accountRequest, HttpKernelInterface::SUB_REQUEST, false);

                        if (400 === $response->getStatusCode()) {
                            $content = json_decode($response->getContent(), true);
                            $authError = json_encode($content['error']);
                        } else {
                            unset($requestParameters['account']);
                        }
                    // Handle login.
                    } else {
                        $router = $this->container->get('router');
                        $requestUri = $router->generate(sprintf('login_check_%s', $authspace), array(), false);
                        $authRequest = $request->duplicate(
                            array(),
                            array(
                                '_username' => $requestParameters['_username'],
                                '_password' => $requestParameters['_password'],
                                '_link' => isset($requestParameters['_link']) ? $requestParameters['_link'] : null,
                                '_remember_me' => isset($requestParameters['_remember_me']) ? 1 : 0,
                                //'_csrf_token' => isset($requestParameters['_csrf_token']) ? $requestParameters['_csrf_token'] : null,
                                '_authspace' => $authspace
                            ),
                            array(),
                            $request->cookies->all(),
                            array(),
                            array_merge(
                                $request->server->all(),
                                array(
                                    'REQUEST_METHOD' => 'POST',
                                    'REQUEST_URI' => $requestUri
                                )
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

                // Handle case of a client forwarded login.
                if ((null === $username && !$account) || !empty($authError)) {
                    $errorPath = $request->query->get('error_path', null);
                    $redirectUri = $request->query->get('redirect_uri');
                    $parsedUri = array_merge(
                        parse_url($redirectUri),
                        parse_url($clientLoginPath)
                    );

                    // Handle proxy connection errors.
                    if (!empty($authError) && $errorPath) {
                        $url = sprintf(
                            '%s://%s%s?%s',
                            $parsedUri['scheme'],
                            $parsedUri['host'],
                            $errorPath,
                            http_build_query(array(
                                'auth_error' => $authError
                            ))
                        );
                    // Redirect to the client login page.
                    } else {
                        /*$loginCsrfToken = $this->container->has('form.csrf_provider')
                            ? $this->container->get('form.csrf_provider')->generateCsrfToken('authenticate')
                            : null
                        ;
                        $registrationCsrfToken = $this->container->has('form.csrf_provider')
                            ? $this->container->get('form.csrf_provider')->generateCsrfToken('registration')
                            : null
                        ;*/

                        $url = sprintf(
                            '%s://%s%s?%s',
                            $parsedUri['scheme'],
                            $parsedUri['host'],
                            isset($parsedUri['path']) ? $parsedUri['path'] : $clientLoginPath,
                            sprintf(
                                '%s&%s',
                                http_build_query(array(
                                    /*'csrf_token' => array(
                                        'login' => $loginCsrfToken,
                                        'registration' => $registrationCsrfToken
                                    ),*/
                                    'redirect_uri' => $redirectUri,
                                    'auth_error' => $authError,
                                    'account' => $account
                                )),
                                $query
                            )
                        );
                    }

                    return new RedirectResponse($url, 302);
                }

                // Replay the authorization request after authentication.
                if ((null !== $username || $account) && empty($authError)) {
                    $queryParameters = $request->query->all();
                    unset($queryParameters['_username']);
                    unset($queryParameters['_password']);
                    unset($queryParameters['account']);
                    unset($queryParameters['logout']);

                    return new RedirectResponse(
                        sprintf(
                            '%s?%s',
                            $this->container->get('router')->generate(
                                'da_oauthserver_authorize_authorizeauthspace',
                                array('authspace' => $authspace)
                            ),
                            http_build_query($queryParameters)
                        ),
                        302
                    );
                }
            }
        }

        return parent::start($request, $authException);
    }
}
