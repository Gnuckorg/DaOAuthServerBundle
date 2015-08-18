<?php

namespace Da\OAuthServerBundle\Security\Firewall;


use Symfony\Component\Security\Http\Firewall\UsernamePasswordFormAuthenticationListener as BaseUsernamePasswordFormAuthenticationListener;
use Symfony\Component\Form\Extension\Csrf\CsrfProvider\CsrfProviderInterface;
use Symfony\Component\HttpFoundation\Request;
use Psr\Log\LoggerInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationFailureHandlerInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationSuccessHandlerInterface;
use Symfony\Component\Security\Http\Session\SessionAuthenticationStrategyInterface;
use Symfony\Component\Security\Http\HttpUtils;
use Symfony\Component\Security\Core\Authentication\AuthenticationManagerInterface;
use Symfony\Component\Security\Core\Exception\InvalidCsrfTokenException;
use Symfony\Component\Security\Core\SecurityContextInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Da\OAuthServerBundle\Security\Authentication\Token\UsernamePasswordToken;

/**
 * UsernamePasswordFormAuthenticationListener is the default implementation of
 * an authentication via a simple form composed of a username and a password.
 *
 * @author Thomas Prelot <tprelot@gmail.com>
 */
class UsernamePasswordFormAuthenticationListener extends BaseUsernamePasswordFormAuthenticationListener
{
    private $csrfProvider;

    /**
     * {@inheritdoc}
     */
    public function __construct(SecurityContextInterface $securityContext, AuthenticationManagerInterface $authenticationManager, SessionAuthenticationStrategyInterface $sessionStrategy, HttpUtils $httpUtils, $providerKey, AuthenticationSuccessHandlerInterface $successHandler, AuthenticationFailureHandlerInterface $failureHandler, array $options = array(), LoggerInterface $logger = null, EventDispatcherInterface $dispatcher = null, CsrfProviderInterface $csrfProvider = null)
    {
        parent::__construct($securityContext, $authenticationManager, $sessionStrategy, $httpUtils, $providerKey, $successHandler, $failureHandler, array_merge(array(
            'link_parameter' => '_link',
        ), $options), $logger, $dispatcher, $csrfProvider);

        $this->csrfProvider = $csrfProvider;
    }

    /**
     * {@inheritdoc}
     */
    protected function attemptAuthentication(Request $request)
    {
        if (null !== $this->csrfProvider) {
            $csrfToken = $request->get($this->options['csrf_parameter'], null, true);

            if (false === $this->csrfProvider->isCsrfTokenValid($this->options['intention'], $csrfToken)) {
                throw new InvalidCsrfTokenException('Invalid CSRF token.');
            }
        }

        if ($this->options['post_only']) {
            $username = trim($request->request->get($this->options['username_parameter'], null, true));
            $password = $request->request->get($this->options['password_parameter'], null, true);
            $linkKey = $request->request->get($this->options['link_parameter'], null, true);
        } else {
            $username = trim($request->get($this->options['username_parameter'], null, true));
            $password = $request->get($this->options['password_parameter'], null, true);
            $linkKey = $request->request->get($this->options['link_parameter'], null, true);
        }

        $request->getSession()->set(SecurityContextInterface::LAST_USERNAME, $username);

        return $this->authenticationManager->authenticate(new UsernamePasswordToken($username, $password, $linkKey, $this->providerKey));
    }
}
