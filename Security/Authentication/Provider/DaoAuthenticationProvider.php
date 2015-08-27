<?php

namespace Da\OAuthServerBundle\Security\Authentication\Provider;

use Symfony\Component\Security\Core\Authentication\Provider\DaoAuthenticationProvider as BaseDaoAuthenticationProvider;
use Symfony\Component\Security\Core\Encoder\EncoderFactoryInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Core\User\UserCheckerInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Symfony\Component\Security\Core\Exception\AuthenticationServiceException;
use Symfony\Component\Security\Core\Exception\BadCredentialsException;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Role\SwitchUserRole;
use Da\OAuthServerBundle\Security\Authentication\Token\UsernamePasswordToken;
use Da\OAuthServerBundle\Entity\User;

/**
 * DaoAuthenticationProvider uses a UserProviderInterface to retrieve the user
 * for a UsernamePasswordToken.
 *
 * @author Thomas Prelot <tprelot@gmail.com>
 */
class DaoAuthenticationProvider extends BaseDaoAuthenticationProvider
{
    protected $providerKey;
    protected $userChecker;
    protected $encoderFactory;
    protected $hideUserNotFoundExceptions;

    /**
     * Constructor.
     *
     * @param UserProviderInterface   $userProvider               An UserProviderInterface instance
     * @param UserCheckerInterface    $userChecker                An UserCheckerInterface instance
     * @param string                  $providerKey                The provider key
     * @param EncoderFactoryInterface $encoderFactory             An EncoderFactoryInterface instance
     * @param bool                    $hideUserNotFoundExceptions Whether to hide user not found exception or not
     */
    public function __construct(UserProviderInterface $userProvider, UserCheckerInterface $userChecker, $providerKey, EncoderFactoryInterface $encoderFactory, $hideUserNotFoundExceptions = true)
    {
        parent::__construct($userProvider, $userChecker, $providerKey, $encoderFactory, $hideUserNotFoundExceptions);

        $this->providerKey = $providerKey;
        $this->userChecker = $userChecker;
        $this->encoderFactory = $encoderFactory;
        $this->hideUserNotFoundExceptions = $hideUserNotFoundExceptions;
    }

    /**
     * {@inheritdoc}
     */
    public function authenticate(TokenInterface $token)
    {
        if (!$this->supports($token)) {
            return;
        }

        $username = $token->getUsername();
        if (empty($username)) {
            $username = 'NONE_PROVIDED';
        }

        try {
            $user = $this->retrieveUser($username, $token);
        } catch (UsernameNotFoundException $e) {
            if ($this->hideUserNotFoundExceptions) {
                throw new BadCredentialsException('Bad credentials', 0, $e);
            }
            $e->setUsername($username);

            throw $e;
        }

        if (!$user instanceof UserInterface) {
            throw new AuthenticationServiceException('retrieveUser() must return a UserInterface.');
        }

        try {
            $this->userChecker->checkPreAuth($user);
            $this->checkCompleteAuthentication($user, $token);
            $this->userChecker->checkPostAuth($user);
        } catch (BadCredentialsException $e) {
            if ($this->hideUserNotFoundExceptions) {
                throw new BadCredentialsException('Bad credentials', 0, $e);
            }

            throw $e;
        }

        $authenticatedToken = new UsernamePasswordToken($user, $token->getCredentials(), $token->getLinkKey(), $this->providerKey, $this->getRoles($user, $token));
        $authenticatedToken->setAttributes($token->getAttributes());

        return $authenticatedToken;
    }

    /**
     * {@inheritdoc}
     */
    public function supports(TokenInterface $token)
    {
        return $token instanceof UsernamePasswordToken && $this->providerKey === $token->getProviderKey();
    }

    /**
     * Check normal and linked authentication.
     *
     * @param UserInterface $user The user.
     * @param UsernamePasswordToken $token The token.
     */
    protected function checkCompleteAuthentication(User $user, UsernamePasswordToken $token)
    {
        $currentUser = $token->getUser();
        if ($currentUser instanceof UserInterface) {
            if ($currentUser->getPassword() !== $user->getPassword()) {
                throw new BadCredentialsException('The credentials were changed from another session.');
            }
        } else {
            if ('' === ($presentedPassword = $token->getCredentials())) {
                throw new BadCredentialsException('The presented password cannot be empty.');
            }

            $linkKey = $token->getLinkKey();

            if ($linkKey) {
                $linkValue = $token->getCredentials();
                $links = $user->getLinks();
                $checked = false;

                foreach ($links as $link) {
                    if ($link->getKey() === $linkKey && $link->getValue() === $linkValue) {
                        $checked = true;
                        break;
                    }
                }

                if (!$checked) {
                    throw new BadCredentialsException('The credentials for the linked authentication are bad.');
                }
            } elseif (!$this->encoderFactory->getEncoder($user)->isPasswordValid($user->getPassword(), $presentedPassword, $user->getSalt())) {
                throw new BadCredentialsException('The presented password is invalid.');
            }
        }
    }

    /**
     * Retrieves roles from user and appends SwitchUserRole if original token contained one.
     *
     * @param UserInterface  $user  The user
     * @param TokenInterface $token The token
     *
     * @return array The user roles
     */
    private function getRoles(UserInterface $user, TokenInterface $token)
    {
        $roles = $user->getRoles();

        foreach ($token->getRoles() as $role) {
            if ($role instanceof SwitchUserRole) {
                $roles[] = $role;

                break;
            }
        }

        return $roles;
    }
}
