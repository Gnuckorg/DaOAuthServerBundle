<?php

namespace Da\OAuthServerBundle\Controller;

use FOS\RestBundle\Routing\ClassResourceInterface;
use FOS\RestBundle\Controller\FOSRestController;
use FOS\RestBundle\Controller\Annotations\RequestParam;
use FOS\RestBundle\Controller\Annotations\QueryParam;
use Da\AuthCommonBundle\Exception\InvalidAccessTokenException;

class AccesstokenController extends FOSRestController implements ClassResourceInterface
{
    /**
     * [GET] /accesstokens/{accessToken}/user
     * Retrieve a user from an access token.
     *
     * @param string $accessToken The access token.
     */
    public function getUserAction($accessToken)
    {
        $soft = (bool)$soft;

        try {
            $data = $this->container->get('da_oauth_server.user_manager.doctrine')
                ->retrieveUserFromAccessToken($accessToken);
            ;
        } catch (InvalidAccessTokenException $e) {
            $view = $this->view($e->getMessage(), 404);
        }
        if (empty($data)) {
            $view = $this->view('User not found.', 404);
        } else {
            $view = $this->view($data, 200);
        }

        return $this->handleView($view);
    }
}
