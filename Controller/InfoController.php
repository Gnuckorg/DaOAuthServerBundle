<?php

namespace Da\OAuthServerBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\JsonResponse;

class InfoController extends Controller
{
    /**
     * @Route("/infos", defaults={"_format"="json"})
     */
    public function getUserInfoAction()
    {
        $user = $this->get('security.context')->getToken()->getUser();

        return new JsonResponse(array(
            'id'       => $user->getId(),
            'username' => $user->getUsername(),
            'email'    => $user->getEmail(),
            'roles'    => json_encode($user->getRoles()),
            'raw'      => $user->getRaw(),
        ));
    }
}
