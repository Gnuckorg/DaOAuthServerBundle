<?php

namespace Da\OAuthServerBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use FOS\RestBundle\Routing\ClassResourceInterface;
use FOS\RestBundle\Controller\FOSRestController;
use FOS\RestBundle\Controller\Annotations\RequestParam;
use FOS\RestBundle\Controller\Annotations\QueryParam;
use FOS\RestBundle\Controller\Annotations\Get;
use FOS\RestBundle\Controller\Annotations\Post;
use Da\OAuthServerBundle\Entity\UserLink;

class UserLinkController extends FOSRestController implements ClassResourceInterface
{
    /**
     * [POST] /users/{user}/links
     * Create a user link.
     *
     * @Post("/users/{user}/links")
     *
     * @RequestParam(name="key", strict=true, description="The key.")
     * @RequestParam(name="value", strict=true, description="The value.")
     *
     * @param string $user  The user.
     * @param string $key   The key.
     * @param string $value The value.
     */
    public function postAction($user, $key, $value)
    {
        try {
            $userManager = $this->container->get('fos_user.user_manager');
            $userObject = $userManager->findUserBy(array('id' => $user));

            if ($userObject) {
                $userLinkManager = $this->container->get('da_oauth_server.user_link_manager');
                $userLink = $userLinkManager->createUserLink($userObject, $key, $value);

                if ($userLink) {
                    $view = $this->view(array('id' => $userLink->getId()), 201);
                } else {
                    $view = $this->view(array('error' => sprintf('Key "%s" already existing for user "%s"', $key, $user)), 409);
                }
            } else {
                $view = $this->view(array('error' => sprintf('User "%s" not found', $user)), 404);
            }
        } catch (\Exception $exception) {
            $view = $this->view(array('error' => $exception->getMessage()), 500);
        }

        return $this->handleView($view);
    }

    /**
     * [GET] /users/{user}/links/{key}
     * Get a user link.
     *
     * @Get("/users/{user}/links/{key}")
     *
     * @param string $user  The user.
     * @param string $key   The key.
     */
    public function getAction($user, $key)
    {
        try {
            $userLinkManager = $this->container->get('da_oauth_server.user_link_manager');
            $userLink = $userLinkManager->findUserLinkBy(array('user' => $user, 'key' => $key));

            if ($userLink) {
                $view = $this->view(
                    array(
                        'id'  => $userLink->getId(),
                        'user'  => $userLink->getUser()->getId(),
                        'key'   => $userLink->getKey(),
                        'value' => $userLink->getValue()
                    ),
                    201
                );
            } else {
                $view = $this->view(array('error' => sprintf('User link for user "%d" and key "%s" not found', $user, $key)), 404);
            }
        } catch (\Exception $exception) {
            $view = $this->view(array('error' => $exception->getMessage()), 500);
        }

        return $this->handleView($view);
    }
}
