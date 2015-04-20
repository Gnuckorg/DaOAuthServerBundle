<?php

namespace Da\OAuthServerBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use FOS\RestBundle\Routing\ClassResourceInterface;
use FOS\RestBundle\Controller\FOSRestController;
use FOS\RestBundle\Controller\Annotations\RequestParam;
use FOS\RestBundle\Controller\Annotations\QueryParam;
use FOS\RestBundle\Controller\Annotations\Get;

class UserController extends FOSRestController implements ClassResourceInterface
{
    protected $client;

    /**
     * [POST] /users
     * Create a user.
     *
     * @RequestParam(name="username", strict=true, description="The username.")
     * @RequestParam(name="email", strict=true, description="The email.")
     * @RequestParam(name="password", strict=true, description="The password.")
     * @RequestParam(name="raw", strict=true, nullable=true, description="The other data in a raw JSON.")
     * @RequestParam(name="enabled", requirements="0|1", default="1", strict=true, description="Enabled user?")
     *
     * @param string $username The username.
     * @param string $email    The email.
     * @param string $password The password.
     * @param string $raw      The other data in a raw JSON.
     * @param string $enabled  Enabled user?
     */
    public function postAction(
        $username,
        $email,
        $password,
        $raw,
        $enabled
    )
    {
        try {
            $request = $this->container->get('request');
            $userManager = $this->container->get('fos_user.user_manager');

            $enabled = (Boolean) $enabled;
            $raw = $this->formatRawData($request, $raw);
            $authspace = $this->getClient($request)->getAuthSpace();

            $user = $userManager->createUser();
            $encoder = $this->container->get('security.encoder_factory')->getEncoder($user);
            $password = $encoder->encodePassword($password, $user->getSalt());

            $user->setUsername($username);
            $user->setEmail($email);
            $user->setPassword($password);
            $user->setRawData($raw);
            $user->setAuthSpace($this->getClient($request)->getAuthSpace());
            $user->setEnabled($enabled);

            $userManager->updateUser($user);

            $view = $this->view(array('id' => $user->getId()), 201);
        } catch (\Exception $exception) {
            $view = $this->view(array('error' => $exception->getMessage()), 400);
        }

        return $this->handleView($view);
    }

    /**
     * [PUT] /users/{id}
     * Modify a user.
     *
     * @RequestParam(name="username", strict=true, description="The username.")
     * @RequestParam(name="email", strict=true, description="The email.")
     * @RequestParam(name="password", strict=true, description="The password.")
     * @RequestParam(name="raw", strict=true, nullable=true, description="The other data in a raw JSON.")
     * @RequestParam(name="enabled", requirements="0|1", default="1", strict=true, description="Enabled user?")
     *
     * @param string $username The username.
     * @param string $email    The email.
     * @param string $password The password.
     * @param string $raw      The other data in a raw JSON.
     * @param string $enabled  Enabled user?
     */
    public function putAction(
        $id,
        $username,
        $email,
        $password,
        $raw,
        $enabled
    )
    {
        try {
            $request = $this->container->get('request');
            $userManager = $this->container->get('fos_user.user_manager');

            $enabled = (Boolean) $enabled;
            $raw = $this->formatRawData($request, $raw);
            $authspace = $this->getClient($request)->getAuthSpace();
            //$user = $userManager->findUserBy(array('username' => $username, 'authSpace' => $authspace->getId()));
            $user = $userManager->findUserBy(array('id' => $id));

            if (null === $user) {
                throw new \LogicException(sprintf(
                    'User "%s"(%s) not found in authspace "%s".',
                    $id,
                    $username,
                    $authspace->getId()
                ));
            }

            $encoder = $this->container->get('security.encoder_factory')->getEncoder($user);
            $password = $encoder->encodePassword($password, $user->getSalt());

            $user->setUsername($username);
            $user->setEmail($email);
            $user->setPassword($password);
            $user->setRawData($raw);
            $user->setAuthSpace($authspace);
            $user->setEnabled($enabled);

            $userManager->updateUser($user);

            $view = $this->view(array(), 204);
        } catch (\LogicException $exception) {
            $view = $this->view(array('error' => $exception->getMessage()), 404);
        } catch (\Exception $exception) {
            $view = $this->view(array('error' => $exception->getMessage()), 400);
        }

        return $this->handleView($view);
    }

    /**
     * [PATCH] /users/{id}
     * Modify a part of a user.
     *
     * @RequestParam(name="username", strict=true, nullable=true, description="The username.")
     * @RequestParam(name="email", strict=true, nullable=true, description="The email.")
     * @RequestParam(name="password", strict=true, nullable=true, description="The password.")
     * @RequestParam(name="raw", strict=true, nullable=true, description="The other data in a raw JSON.")
     * @RequestParam(name="enabled", requirements="0|1", strict=true, nullable=true, description="Enabled user?")
     *
     * @param string $username The username.
     * @param string $email    The email.
     * @param string $password The password.
     * @param string $raw      The other data in a raw JSON.
     * @param string $enabled  Enabled user?
     */
    public function patchAction(
        $id,
        $username,
        $email,
        $password,
        $raw,
        $enabled
    )
    {
        try {
            $request = $this->container->get('request');
            $userManager = $this->container->get('fos_user.user_manager');

            $user = $userManager->findUserBy(array('id' => $id));
            $enabled = (Boolean) $enabled;
            $oldRaw = $user->getRawData();
            if (null === $oldRaw) {
                $oldRaw = array();
            } else if (!is_array($oldRaw)) {
                $oldRaw = array('content' => $oldRaw);
            }
            $raw = $this->formatRawData($request, $raw, $oldRaw);
            $authspace = $this->getClient($request)->getAuthSpace();

            if (null === $user) {
                throw new \LogicException(sprintf(
                    'User "%s" not found in authspace "%s".',
                    $id,
                    $authspace->getId()
                ));
            }

            $encoder = $this->container->get('security.encoder_factory')->getEncoder($user);
            if (null !== $password) {
                $password = $encoder->encodePassword($password, $user->getSalt());
                $user->setPassword($password);
            }

            if (null !== $username) {
                $user->setUsername($username);
            }
            if (null !== $email) {
                $user->setEmail($email);
            }
            if (null !== $raw) {
                $user->setRawData($raw);
            }
            if (null !== $authspace) {
                $user->setAuthSpace($authspace);
            }
            if (null !== $enabled) {
                $user->setEnabled($enabled);
            }

            $userManager->updateUser($user);

            $view = $this->view(array(), 204);
        } catch (\LogicException $exception) {
            $view = $this->view(array('error' => $exception->getMessage()), 404);
        } catch (\Exception $exception) {
            $view = $this->view(array('error' => $exception->getMessage()), 400);
        }

        return $this->handleView($view);
    }

    /**
     * [DELETE] /users/{id}
     * Delete a user (disabling).
     */
    public function deleteAction($id)
    {
        try {
            $userManager = $this->container->get('fos_user.user_manager');

            $user = $userManager->findUserBy(array('id' => $id));

            if (null === $user) {
                throw new \LogicException(sprintf(
                    'User "%s" not found in authspace "%s".',
                    $id,
                    $authspace->getId()
                ));
            }

            $raw = $user->getRawData();
            if (null === $raw || empty($raw)) {
                $raw = array();
            }
            if (is_array($raw)) {
                $now = new \DateTime();
                $raw['deletedAt'] = $now->format(\DateTime::ISO8601);
                $user->setRawData($raw);
            }
            $user->setEnabled(false);

            $userManager->updateUser($user);

            $view = $this->view(array(), 204);
        } catch (\Exception $exception) {
            $view = $this->view(array('error' => $exception->getMessage()), 400);
        }

        return $this->handleView($view);
    }

    /**
     * [GET] /users/id
     * Get a user id from a username.
     *
     * @Get("/users/{id")
     *
     * @QueryParam(name="id", strict=true, description="The id.")
     *
     * @param string $id The id.
     */
    public function getAction($id)
    {
        try {
            $request = $this->container->get('request');
            $userManager = $this->container->get('fos_user.user_manager');

            $authspace = $this->getClient($request)->getAuthSpace();

            $user = $userManager->findUserBy(array('id' => $id, 'authSpace' => $authspace->getId()));

            if (null === $user) {
                $view = $this->view(array('error' => sprintf('User "%s" not found', $id)), 404);
            } else {
                $view = $this->view($user, 200);
            }
        } catch (\Exception $exception) {
            $view = $this->view(array('error' => $exception->getMessage()), 400);
        }

        return $this->handleView($view);
    }

    /**
     * [GET] /users/id
     * Get a user id from a username.
     *
     * @Get("/users/id")
     *
     * @QueryParam(name="username", strict=true, description="The username.")
     *
     * @param string $username The username.
     */
    public function getFromUsernameAction($username)
    {
        try {
            $request = $this->container->get('request');
            $userManager = $this->container->get('fos_user.user_manager');

            $authspace = $this->getClient($request)->getAuthSpace();

            $user = $userManager->findUserBy(array('username' => $username, 'authSpace' => $authspace->getId()));

            if (null === $user) {
                $view = $this->view(array('error' => sprintf('User "%s" not found', $username)), 404);
            } else {
                $view = $this->view(array('id' => $user->getId()), 200);
            }
        } catch (\Exception $exception) {
            $view = $this->view(array('error' => $exception->getMessage()), 400);
        }

        return $this->handleView($view);
    }

    /**
     * [GET] /users/available
     * Modify a user.
     *
     * @Get("/users/available")
     *
     * @QueryParam(name="username", strict=true, description="The username.")
     *
     * @param string $username The username.
     */
    public function isAvailableAction($username)
    {
        $available = false;

        try {
            $request = $this->container->get('request');
            $userManager = $this->container->get('fos_user.user_manager');

            $authspace = $this->getClient($request)->getAuthSpace();

            $user = $userManager->findUserBy(array('username' => $username, 'authSpace' => $authspace->getId()));

            if (null === $user) {
                $available = true;
            }

            $view = $this->view(array('available' => $available), 200);
        } catch (\Exception $exception) {
            $view = $this->view(array('error' => $exception->getMessage()), 400);
        }

        return $this->handleView($view);
    }

    /**
     * Format raw data.
     *
     * @param Request $request The request.
     * @param mixed   $raw     The initial raw data.
     * @param mixed   $oldRaw  The old raw data to merge.
     *
     * @return array The raw data.
     *
     * @throws \InvalidArgumentException if the passed raw has not a json format.
     */
    protected function formatRawData(Request $request, $raw, array $oldRaw = array())
    {
        if (null === $raw || empty($raw)) {
            $raw = array();
        } else {
            $raw = json_decode($raw, true);

            if (null === $raw || !is_array($raw)) {
                throw new \InvalidArgumentException('The raw parameter must be a JSON string.');
            }
        }

        foreach ($request->request->all() as $key => $value) {
            if (!in_array(
                $key,
                array(
                    'username',
                    'email',
                    'password',
                    'authspace',
                    'raw',
                    'enabled'
                )
            )) {
                if (!isset($raw[$key])) {
                    $raw[$key] = $value;
                }
            }
        }

        $raw = array_merge($oldRaw, $raw);

        return $raw;
    }

    /**
     * Get the current client.
     *
     * @param Request $request The request.
     *
     * @return \FOS\OAuthServerBundle\Model\ClientInterface The client.
     */
    protected function getClient(Request $request)
    {
        if (null === $this->client) {
            $apiToken = $this->getApiTokenFromHeaders($request, false);

            $client = $this->container
                ->get('da_oauth_server.client_manager.doctrine')
                ->retrieveClientByApiToken($apiToken)
            ;

            if (null === $client) {
                throw new NotFoundHttpException('Client not found.');
            }

            $this->client = $client;
        }

        return $this->client;
    }

    /**
     * Get the API token from the header.
     *
     * @param Request $request           The request.
     * @param boolean $removeFromRequest Should remove the token form the request?
     *
     * @return string The API token or null if non-existent.
     */
    protected function getApiTokenFromHeaders(Request $request, $removeFromRequest)
    {
        $token = null;
        if (!$request->headers->has('X-API-Security-Token')) {
            // The Authorization header may not be passed to PHP by Apache.
            // Trying to obtain it through apache_request_headers().
            if (function_exists('apache_request_headers')) {
                $headers = apache_request_headers();

                if (isset($headers['X-API-Security-Token'])) {
                   $token = $headers['X-API-Security-Token'];
                }
            }
        } else {
            $token = $request->headers->get('X-API-Security-Token');
        }

        if (!$token) {
            return null;
        }

        if ($removeFromRequest) {
            $request->headers->remove('X-API-Security-Token');
        }

        return $token;
    }
}
