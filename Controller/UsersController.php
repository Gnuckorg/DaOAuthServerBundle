<?php

namespace Da\OAuthServerBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

/**
 * @Route("/api")
 * @Template()
 */
class UsersController extends Controller
{
    /**
     * @Route("/user", defaults={"_format"="json"})
     */
    public function getUserAction()
    {
        $user = $this->get('security.context')->getToken()->getUser();

        return array
        	(
        		'username' => $user->getUsername(),
        		'email' => $user->getEmail(),
                'raw' => $user->getRaw()
        	);
    }
}
