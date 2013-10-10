<?php

namespace Da\OAuthServerBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

/**
 * @Template()
 */
class InfoController extends Controller
{
    /**
     * @Route("/infos", defaults={"_format"="json"})
     */
    public function getUserInfoAction()
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
