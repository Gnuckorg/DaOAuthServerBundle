<?php

namespace Da\OAuthServerBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use FOS\RestBundle\Routing\ClassResourceInterface;
use FOS\RestBundle\Controller\FOSRestController;
use FOS\RestBundle\Controller\Annotations\RequestParam;
use FOS\RestBundle\Controller\Annotations\QueryParam;
use FOS\RestBundle\Controller\Annotations\Put;

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
            $enabled = (Boolean) $enabled;

            if (null === $raw || empty($raw)) {
                $raw = array();
            } else {
                $raw = json_decode($raw, true);

                if (null === $raw || !is_array($raw)) {
                    throw new \InvalidArgumentException('The raw parameter must be a JSON string.');
                }
            }

            $request = $this->container->get('request');
            $userManager = $this->container->get('fos_user.user_manager');
            $parameters = $request->request->all();

            foreach ($parameters as $key => $value) {
                if (!in_array(
                    $key,
                    array(
                        'username',
                        'email',
                        'password',
                        'raw',
                        'enabled'
                    )
                )) {
                    if (!isset($raw[$key])) {
                        $raw[$key] = $value;
                    }
                }
            }

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
     * [PUT] /users/{username}
     * Modify a user.
     *
     * @Put("/users")
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
        $username,
        $email,
        $password,
        $raw,
        $enabled
    )
    {
        try {
            $enabled = (Boolean) $enabled;

            if (null === $raw || empty($raw)) {
                $raw = array();
            } else {
                $raw = json_decode($raw, true);

                if (null === $raw || !is_array($raw)) {
                    throw new \InvalidArgumentException('The raw parameter must be a JSON string.');
                }
            }

            $request = $this->container->get('request');
            $userManager = $this->container->get('fos_user.user_manager');
            $parameters = $request->request->all();

            foreach ($parameters as $key => $value) {
                if (!in_array(
                    $key,
                    array(
                        'username',
                        'email',
                        'password',
                        'raw',
                        'enabled'
                    )
                )) {
                    if (!isset($raw[$key])) {
                        $raw[$key] = $value;
                    }
                }
            }

            $authspace = $this->getClient($request)->getAuthSpace();
            $user = $userManager->findUserBy(array('username' => $username, 'authSpace' => $authspace->getId()));

            if (null === $user) {
                throw new \LogicException(sprintf(
                    'User "%s" not found in authspace "%s".',
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
     * @return ClientInterface
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
