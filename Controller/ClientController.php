<?php

namespace Da\OAuthServerBundle\Controller;

use FOS\RestBundle\Routing\ClassResourceInterface;
use FOS\RestBundle\Controller\FOSRestController;
use FOS\RestBundle\Controller\Annotations\RequestParam;
use FOS\RestBundle\Controller\Annotations\QueryParam;
use Da\AuthCommonBundle\Exception\InvalidApiTokenException;

class ClientController extends FOSRestController implements ClassResourceInterface
{
    /**
     * [GET] /clients/{apiToken}
     * Retrieve a client from its API token.
     *
     * @param string $apiToken The API token.
     */
    public function getAction($apiToken)
    {
        try {
            $data = $this->container->get('da_oauth_server.client_manager.doctrine')
                ->retrieveClientByApiToken($apiToken)
            ;
        } catch (InvalidApiTokenException $e) {
            $view = $this->view($e->getMessage(), 404);
        }

        if (empty($data)) {
            $view = $this->view('Client not found.', 404);
        } else {
            $view = $this->view($data, 200);
        }

        return $this->handleView($view);
    }
}
