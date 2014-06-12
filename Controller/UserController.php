<?php

namespace Da\OAuthServerBundle\Controller;

use FOS\RestBundle\Routing\ClassResourceInterface;
use FOS\RestBundle\Controller\FOSRestController;
use FOS\RestBundle\Controller\Annotations\RequestParam;
use FOS\RestBundle\Controller\Annotations\QueryParam;

class UserController extends FOSRestController implements ClassResourceInterface
{
    /**
     * [GET] /users/{id}
     * Retrieve a user.
     *
     * @QueryParam(name="soft", requirements="0|1", default="1", strict=true, description="Using the soft deletion mechanism?")
     *
     * @param string $id   The user id.
     * @param string $soft Using the soft deletion mechanism?
     */
    public function getAction($id, $soft)
    {
        $soft = (bool)$soft;

        $data = $this->container->get('da_oauth_server.user_manager.doctrine')
            ->retrieveUser($id, $soft)
        ;

        if (empty($data)) {
            $view = $this->view('User not found.', 404);
        } else {
            $view = $this->view($data, 200);
        }

        return $this->handleView($view);
    }
}
